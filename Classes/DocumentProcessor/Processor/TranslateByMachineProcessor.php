<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\DocumentProcessor\Processor;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Werkraum\DeeplTranslate\DeepL\DeepL;
use Werkraum\DeeplTranslate\DocumentProcessor\DocumentProcessorInterface;
use Werkraum\DeeplTranslate\StringUtility;

class TranslateByMachineProcessor implements DocumentProcessorInterface
{
    private string $text;

    public function extractFromDocument(\DOMDocument $document): void
    {
        $request = $this->getRequest();
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        /** @var SiteLanguage $originalLanguage */
        $originalLanguage = $request->getAttribute('language', $site->getDefaultLanguage());
        $requestedLanguage = strtoupper(trim((string) ($request->getParsedBody()['deepl'] ?? $request->getQueryParams()['deepl'] ?? null)));
        $targetSourceLanguage = \Werkraum\DeeplTranslate\Site\Entity\SiteLanguage::getDeeplSourceLanguage($originalLanguage) ?? (int)$site->getConfiguration()['default_deepl_source_language'];

        if ($originalLanguage->getLanguageId() !== $targetSourceLanguage) {
            $language = $site->getLanguageById($targetSourceLanguage);
        } else {
            $language = $originalLanguage;
        }

        $deepL = new DeepL();
        $currentUri = $request->getUri();

        $translatedByMachineText = (string)$site->getConfiguration()['deepl_translated_by_machine'];
        $translatedByMachineText = \str_replace('originalLink', 'originalLink -> f:format.raw()', $translatedByMachineText);

        $languageData = $deepL->languageData($requestedLanguage);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<f:format.raw><f:spaceless>' . $translatedByMachineText . '</f:spaceless></f:format.raw>');
        $view->assignMultiple([
            'source' => $language->getNavigationTitle(),
            'target' => $languageData['name'],
            'originalLink' => "<a target='_parent' hreflang='{$language->getHreflang()}' href='{$currentUri->withQuery('')->__toString()}'>{$language->getTitle()} version</a>"
        ]);

        $this->text = $view->render();
    }

    public function getTextsForTranslation(): array
    {
        $request = $this->getRequest();
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        $translateTranslatedByMachineText = (bool)$site->getConfiguration()['deepl_translate_translated_by_machine'];
        if ($translateTranslatedByMachineText) {
            return [$this->text];
        }

        return [];
    }

    public function setTranslations(array $texts): void
    {
        if (isset($texts[0])) {
            $this->text = $texts[0];
        }
    }

    public function embedInDocument(\DOMDocument $document): void
    {
        $request = $this->getRequest();
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        $translatedByMachineTarget = (string)$site->getConfiguration()['deepl_translated_by_machine_target'];

        $xpath = new \DOMXPath($document);
        $target = $xpath->query("//*[@id='$translatedByMachineTarget']")->item(0);
        if ($target instanceof \DOMElement) {
            $tempDoc = new \DOMDocument('1.0', 'UTF-8');
            @$tempDoc->loadHTML(StringUtility::normalizeUtf8($this->text));
            $tempElement = $tempDoc->documentElement;
            $tempNode = $document->importNode($tempElement, true);

            $target->appendChild($tempNode);
        }
    }

    public function sendMultipleTranslationRequests(): bool
    {
        return false;
    }

    public static function getPriority(): int
    {
        return 10;
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
    }

}
