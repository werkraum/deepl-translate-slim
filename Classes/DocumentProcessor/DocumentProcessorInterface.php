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

namespace Werkraum\DeeplTranslate\DocumentProcessor;

interface DocumentProcessorInterface
{
    public function extractFromDocument(\DOMDocument $document): void;

    public function getTextsForTranslation(): array;

    public function setTranslations(array $texts): void;

    public function embedInDocument(\DOMDocument $document): void;

    public function sendMultipleTranslationRequests(): bool;

    public static function getPriority(): int;
}
