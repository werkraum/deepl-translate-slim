<?php

/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Dom;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Werkraum\DeeplTranslate\StringUtility;

class DomDocumentService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function fromStringContent(
        string $content,
        string $version = '1.0',
        string $encoding = 'UTF-8',
        bool $preserveWhitespace = true,
        bool $formatOutput = false,
        bool $throwExceptionOnFailure = false
    ): \DomDocument {
        $domDocument = new \DOMDocument($version, $encoding);
        $domDocument->preserveWhiteSpace = $preserveWhitespace;
        $domDocument->formatOutput = $formatOutput;
        $document = @$domDocument->loadHTML(StringUtility::normalizeUtf8($content));
        if ($document === false) {
            $this->logger?->error('Could not parse HTML content', ['content' => substr($content, 0, 250) ]);
            if ($throwExceptionOnFailure) {
                throw new \RuntimeException('Could not parse HTML content');
            }
        }
        return $domDocument;
    }
}
