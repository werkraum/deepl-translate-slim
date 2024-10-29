<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Middleware\Event;

class BeforeTranslatingMainContentEvent
{

    public function __construct(protected string $mainContent)
    {
    }

    public function getMainContent(): string
    {
        return $this->mainContent;
    }

    public function setMainContent(string $mainContent): void
    {
        $this->mainContent = $mainContent;
    }
}
