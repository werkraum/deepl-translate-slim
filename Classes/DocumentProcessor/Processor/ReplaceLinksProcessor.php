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
use Werkraum\DeeplTranslate\DocumentProcessor\DocumentProcessorInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ReplaceLinksProcessor implements DocumentProcessorInterface
{
    public function extractFromDocument(\DOMDocument $document): void
    {
    }

    public function getTextsForTranslation(): array
    {
        return [];
    }

    public function setTranslations(array $texts): void
    {
    }

    public function sendMultipleTranslationRequests(): bool
    {
        return false;
    }

    public function embedInDocument(\DOMDocument $document): void
    {
        $request = $this->getRequest();
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        $requestedLanguage = strtoupper(trim((string) ($request->getParsedBody()['deepl'] ?? $request->getQueryParams()['deepl'] ?? null)));

        $replaceLinks = (bool)$site->getConfiguration()['deepl_replace_links'];
        if ($replaceLinks) {
            $xpath = new \DOMXPath($document);
            $attributesToIgnore = GeneralUtility::trimExplode(',', (string)$site->getConfiguration()['deepl_replace_links_attribute']);

            $baseHost = $site->getBase()->getHost();

            if ($baseHost === '') {
                $baseHost = $site->getBase()->getPath();
            }

            // add deepl param to all links
            $links = $xpath->query('//a[@href]');
            foreach ($links as $link) {
                if ($link instanceof \DOMElement) {
                    foreach ($attributesToIgnore as $item) {
                        if ($link->hasAttribute($item)) {
                            continue 2;
                        }
                    }

                    $href = $link->getAttribute('href');
                    if (!str_starts_with($href, '/')) {
                        $host = \parse_url($href, \PHP_URL_HOST);
                        if ($host === '') {
                            continue;
                        }
                        if ($host === false) {
                            continue;
                        }
                        if ($host === null) {
                            continue;
                        }
                        if ($host !== $baseHost) {
                            continue;
                        }
                    }

                    $query = \parse_url($href, \PHP_URL_QUERY);

                    $href .= $query ? '&' : '?';
                    $href .= "deepl=$requestedLanguage";

                    $link->setAttribute('href', $href);
                }
            }
        }
    }

    public static function getPriority(): int
    {
        return 100;
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
    }
}
