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

            if (empty($baseHost)) {
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
                    if (strpos($href, '/') !== 0) {
                        $host = \parse_url($href, \PHP_URL_HOST);
                        if (empty($host)) {
                            continue;
                        }
                        if ($host !== $baseHost) {
                            continue;
                        }
                    }
                    $deeplParam = "deepl=$requestedLanguage";
                    $link->setAttribute('href', $this->extendLink($href, $deeplParam));
                }
            }
            // add deepl param to all forms
            $links = $xpath->query('//form[@action]');
            foreach ($links as $link) {
                if ($link instanceof \DOMElement) {
                    foreach ($attributesToIgnore as $item) {
                        if ($link->hasAttribute($item)) {
                            continue 2;
                        }
                    }

                    $href = $link->getAttribute('action');
                    if (strpos($href, '/') !== 0) {
                        $host = \parse_url($href, \PHP_URL_HOST);
                        if (empty($host)) {
                            continue;
                        }
                        if ($host !== $baseHost) {
                            continue;
                        }
                    }
                    $deeplParam = "deepl=$requestedLanguage";
                    $link->setAttribute('action', $this->extendLink($href, $deeplParam));
                }
            }
        }
    }

    private function extendLink(string $link, string $deeplParam): string
    {
        $parsed_url = \parse_url($link);
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = $parsed_url['host'] ?? '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = $parsed_url['user'] ?? '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parsed_url['path'] ?? '';
        $query    = isset($parsed_url['query'])
            ? '?' . $parsed_url['query'] . '&' . $deeplParam
            : '?' . $deeplParam;
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
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
