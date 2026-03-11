<?php

/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use Werkraum\DeeplTranslate\DeepL\DeepL;
use Werkraum\DeeplTranslate\DocumentProcessor\DocumentProcessorChain;
use Werkraum\DeeplTranslate\Dom\DomDocumentService;
use Werkraum\DeeplTranslate\Middleware\Event\BeforeSettingTranslationIntoCacheEvent;
use Werkraum\DeeplTranslate\Middleware\Event\BeforeTranslatingMainContentEvent;
use Werkraum\DeeplTranslate\Middleware\Event\CacheIdentifierEvent;
use Werkraum\DeeplTranslate\Middleware\Event\IsTranslationAllowedEvent;
use Werkraum\DeeplTranslate\Site\Entity\SiteLanguage as DeeplSiteLanguage;
use Werkraum\DeeplTranslate\StringUtility;

class TranslationMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?DeepL $deepL = null;

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected DocumentProcessorChain $processorChain,
        protected FrontendInterface $cache,
        private Context $context,
        protected DomDocumentService $domDocumentService,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $backendUserLoggedIn = $this->context->getPropertyFromAspect('backend.user', 'isLoggedIn', false);
        // do not translate anything if a backenduser is logged in to prevent admin_panel overlay in translation result or anything similar!
        if ($backendUserLoggedIn) {
            return $handler->handle($request);
        }
        /** @var IsTranslationAllowedEvent $event */
        $event = $this->eventDispatcher->dispatch(new IsTranslationAllowedEvent($request));
        if (!$event->isAllowed()) {
            return $handler->handle($request);
        }

        $response = null;
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        /** @var PageArguments $pageArguments */
        $pageArguments = $request->getAttribute('routing', null);
        /** @var SiteLanguage $originalLanguage */
        $originalLanguage = $request->getAttribute('language', $site->getDefaultLanguage());
        $requestedLanguage = strtoupper(trim((string)($request->getParsedBody()['deepl'] ?? $request->getQueryParams()['deepl'] ?? null)));
        $targetSourceLanguage = DeeplSiteLanguage::getDeeplSourceLanguage($originalLanguage) ?? (int)$site->getConfiguration()['default_deepl_source_language'];

        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('wr_deepl_translate');

        try {
            // render the site with a specified language if not disabled
            if ($originalLanguage->getLanguageId() !== $targetSourceLanguage) {
                $language = $site->getLanguageById($targetSourceLanguage);
                $subRequest = $request
                    ->withAttribute('originalLanguage', $originalLanguage)
                    ->withAttribute('language', $language)
                ;
            } else {
                $language = $originalLanguage;
                $subRequest = clone $request;
            }
            $response = $handler->handle($subRequest);

            // prepare the content for deepl
            $body = $response->getBody();
            $body->rewind();
            $contents = $response->getBody()->getContents();

            $cacheIdentifier = md5((string)(clone $subRequest->getUri())
                ->withQuery('')
                ->withFragment(''));

            $cacheIdentifier = md5($cacheIdentifier . $requestedLanguage . $targetSourceLanguage);

            /** @var CacheIdentifierEvent $event */
            $event = $this->eventDispatcher->dispatch(new CacheIdentifierEvent($subRequest, $cacheIdentifier));
            $cacheIdentifier = $event->getCacheIdentifier();

            if (($translation = $this->cache->get($cacheIdentifier)) === false) {
                $this->deepL = new DeepL();

                $domDocument = $this->domDocumentService->fromStringContent($contents);

                foreach ($this->processorChain->getProcessors() as $processor) {
                    $processor->extractFromDocument($domDocument);
                }

                $mainContent = $domDocument->saveHTML();
                unset($domDocument);
                // remove whitespace between tags... libxml is not very helpful since it's very depending on the installed version
                $mainContent = preg_replace('/>\s+</', '><', $mainContent);
                // replace non-closing <br> with self-closing one <br/>
                $mainContent = str_replace('<br>', '<br/>', $mainContent);

                // request translation
                $supportsFormality = $this->deepL->supportsFormality($requestedLanguage);

                foreach ($this->processorChain->getProcessors() as $processor) {
                    $toTranslate = $processor->getTextsForTranslation();

                    if (empty($toTranslate)) {
                        continue;
                    }

                    if ($processor->sendMultipleTranslationRequests()) {
                        foreach ($toTranslate as $text) {
                            $translation = $this->deepL->translate(
                                $text,
                                $language->getLocale()->getLanguageCode(),
                                $requestedLanguage,
                                $config['tag_handling'] ?? 'xml',
                                $config['ignoreTags'],
                                $supportsFormality ? $config['formality'] : '',
                                $config['splitSentences'],
                                $config['preserveFormatting'],
                                $config['nonSplittingTags'],
                                $config['outlineDetection'],
                                $config['splittingTags'],
                                '',
                                $config['tag_handling_version'] ?? 'v1',
                            );
                            $translations []= $translation[0]['text'];
                            unset($translation);
                        }
                    } else {
                        $translations = $this->deepL->translate(
                            $toTranslate,
                            $language->getLocale()->getLanguageCode(),
                            $requestedLanguage,
                            $config['tag_handling'] ?? 'xml',
                            $config['ignoreTags'],
                            $supportsFormality ? $config['formality'] : '',
                            $config['splitSentences'],
                            $config['preserveFormatting'],
                            $config['nonSplittingTags'],
                            $config['outlineDetection'],
                            $config['splittingTags'],
                            '',
                            $config['tag_handling_version'] ?? 'v1',
                        );

                        $translations = \array_map(static fn ($i) => $i['text'], $translations);
                    }
                    $processor->setTranslations($translations);
                    unset($translations);
                }

                /** @var BeforeTranslatingMainContentEvent $event */
                $event = $this->eventDispatcher->dispatch(new BeforeTranslatingMainContentEvent($mainContent));
                $mainContent = $event->getMainContent();

                $mainTranslation = $this->deepL->translate(
                    $mainContent,
                    $language->getLocale()->getLanguageCode(),
                    $requestedLanguage,
                    $config['tag_handling'] ?? 'xml',
                    $config['ignoreTags'],
                    $supportsFormality ? $config['formality'] : '',
                    $config['splitSentences'],
                    $config['preserveFormatting'],
                    $config['nonSplittingTags'],
                    $config['outlineDetection'],
                    $config['splittingTags'],
                    '',
                    $config['tag_handling_version'] ?? 'v1',
                );
                $mainTranslation = $mainTranslation[0]['text'];

                // build the translated response
                $translatedDocument = $this->domDocumentService->fromStringContent($mainTranslation);

                foreach (\array_reverse($this->processorChain->getProcessors()) as $processor) {
                    $processor->embedInDocument($translatedDocument);
                }

                $translation = $translatedDocument->saveHTML();
                unset($translatedDocument);
                // put result into cache
                $tags = [
                    "deeplPageId_{$pageArguments->getPageId()}", // flush by page id
                    "deeplPage_{$pageArguments->getPageId()}_{$requestedLanguage}", // used for cache overview
                    'deepl_translations', // flush all
                ];

                /** @var BeforeSettingTranslationIntoCacheEvent $event */
                $event = $this->eventDispatcher->dispatch(new BeforeSettingTranslationIntoCacheEvent($translation, $tags, $cacheIdentifier));
                $this->cache->set(
                    $event->getCacheIdentifier(),
                    $event->getText(),
                    $event->getTags()
                );
            }

            if (!$response instanceof ResponseInterface) {
                $response = new Response();
            }

            // extract stylesheets and scripts from original response and embed into translation to ensure
            // they are valid, e.g. after cache invalidation through TYPO3 backend
            $domDocument = $this->domDocumentService->fromStringContent($contents);

            $xpath = new \DOMXPath($domDocument);
            $xpathClassQuery = \PhpCss::toXpath('link[rel=stylesheet]');
            $sources = $xpath->query($xpathClassQuery);
            $styleSheets = [];
            /** @var \DOMNode $source */
            foreach ($sources as $source) {
                $styleSheets []= $source->ownerDocument->saveHTML($source);
                $source->parentNode->removeChild($source);
            }

            $xpathClassQuery = \PhpCss::toXpath('script[src]');
            $sources = $xpath->query($xpathClassQuery);
            $scripts = [];
            /** @var \DOMNode $source */
            foreach ($sources as $source) {
                $scripts []= $source->ownerDocument->saveHTML($source);
                $source->parentNode->removeChild($source);
            }

            $translatedDoc = $this->domDocumentService->fromStringContent($translation);

            $xpath = new \DOMXPath($translatedDoc);
            $xpathClassQuery = \PhpCss::toXpath('link[rel=stylesheet],script[src]');
            $sources = $xpath->query($xpathClassQuery);
            foreach ($sources as $source) {
                // delete any css/js file
                $source->parentNode->removeChild($source);
            }
            // append stylesheets to head
            $headNode = $translatedDoc->getElementsByTagName('head')
                ->item(0);
            foreach ($styleSheets as $element) {
                $tempDoc = $this->domDocumentService->fromStringContent($element);
                $tempElement = $tempDoc->documentElement;
                $tempNode = $translatedDoc->importNode($tempElement, true);
                $headNode->appendChild($tempNode->childNodes->item(0)->childNodes->item(0));
            }
            // append scripts to body
            $bodyNode = $translatedDoc->getElementsByTagName('body')
                ->item(0);
            foreach ($scripts as $element) {
                $tempDoc = $this->domDocumentService->fromStringContent($element);
                $tempElement = $tempDoc->documentElement;
                $tempNode = $translatedDoc->importNode($tempElement, true);
                $bodyNode->appendChild($tempNode->childNodes->item(0)->childNodes->item(0));
            }

            $isRtl = array_key_exists($requestedLanguage, array_flip(DeepL::RTL_LANGUAGES));
            if ($isRtl) {
                $translatedDoc->documentElement->setAttribute('dir', 'rtl');
            } else {
                $translatedDoc->documentElement->setAttribute('dir', 'ltr');
            }
            $translatedDoc->documentElement->setAttribute('lang', $requestedLanguage);

            $body = new Stream('php://temp', 'rw');
            $body->write($translatedDoc->saveHTML());
            $response = $response->withBody($body);
        } catch (\Exception $exception) {
            if ($response instanceof ResponseInterface) {
                $this->logger->critical($exception->getMessage(), [
                    'content-length' => (string)$response->getBody()->getSize(),
                ]);
            } else {
                $this->logger->critical($exception->getMessage());
            }
        }

        // if no translation was found or created, just return the original response
        if (!$response instanceof ResponseInterface) {
            $response = $handler->handle($request);
        }

        if ((new Typo3Version())->getMajorVersion() < 13) {
            if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
                if (
                    (!isset($GLOBALS['TSFE']->config['config']['enableContentLengthHeader']) || $GLOBALS['TSFE']->config['config']['enableContentLengthHeader'])
                    && !$this->context->getPropertyFromAspect('backend.user', 'isLoggedIn', false) && !$this->context->getPropertyFromAspect('workspace', 'isOffline', false)
                ) {
                    $response = $response->withHeader('Content-Length', (string)$response->getBody()->getSize());
                }
            }
        } else {
            $typoScriptConfigArray = $request->getAttribute('frontend.typoscript')?->getConfigArray();
            if (
                (!isset($typoScriptConfigArray['enableContentLengthHeader']) || $typoScriptConfigArray['enableContentLengthHeader'])
                && !$this->context->getPropertyFromAspect('backend.user', 'isLoggedIn', false)
                && !$this->context->getPropertyFromAspect('workspace', 'isOffline', false)
            ) {
                $response = $response->withHeader('Content-Length', (string)$response->getBody()->getSize());
            }
        }

        return $response;
    }
}
