<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

/*
 * This file is part of TYPO3 CMS-based extension "wr_deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

/*
 * This file is part of TYPO3 CMS-based extension "wr_deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\DocumentProcessor\Processor;

use PhpCss\Exception\ParserException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Site\Entity\Site;
use Werkraum\DeeplTranslate\DocumentProcessor\DocumentProcessorInterface;
use Werkraum\DeeplTranslate\StringUtility;

class ExcludeByCssProcessor implements DocumentProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $excludedElements = [];

    public function extractFromDocument(\DOMDocument $document): void
    {
        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site', null);
        $excludeByCssSelector = (string)$site->getConfiguration()['deepl_exclude_elements_by_selector'];
        if ($excludeByCssSelector !== '') {
            $xpath = new \DOMXPath($document);
            $excludeByCssSelector = explode(',', $excludeByCssSelector);
            $excludedElementCounter = 0;
            foreach ($excludeByCssSelector as $cssSelector) {
                try {
                    $xpathClassQuery = \PhpCss::toXpath($cssSelector);
                    $excludedElements = $xpath->query($xpathClassQuery);
                    /** @var \DOMElement $excludedElement */
                    foreach ($excludedElements as $excludedElement) {
                        $this->excludedElements [$excludedElementCounter]= $excludedElement->ownerDocument->saveHTML($excludedElement);
                        $placeHolderElement = $document->createElement('excluded', $excludedElementCounter);
                        $excludedElement->parentNode->replaceChild($placeHolderElement, $excludedElement);
                        $excludedElementCounter++;
                    }
                } catch (ParserException $exception) {
                    $this->logger->error('could not parse css selector', [
                        'selector' => $cssSelector,
                        'message' => $exception->getMessage()
                    ]);
                }
            }
        }
    }

    public function getTextsForTranslation(): array
    {
        return [];
    }

    public function setTranslations(array $texts): void
    {
    }

    public function embedInDocument(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);
        foreach ($this->excludedElements as $index => $element) {
            $tempDoc = new \DOMDocument('1.0', 'UTF-8');
            @$tempDoc->loadHTML(StringUtility::normalizeUtf8((string) $element));
            $tempElement = $tempDoc->documentElement;
            $tempNode = $document->importNode($tempElement, true);

            $placeHolderNode = $xpath->query("//excluded[./text()='$index']")->item(0);
            if ($placeHolderNode instanceof \DOMNode) {
                $placeHolderNode->parentNode->replaceChild($tempNode, $placeHolderNode);
            }
        }
    }

    public function sendMultipleTranslationRequests(): bool
    {
        return false;
    }

    public static function getPriority(): int
    {
        return 100;
    }
}
