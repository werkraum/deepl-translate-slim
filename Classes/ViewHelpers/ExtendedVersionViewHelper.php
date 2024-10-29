<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Werkraum\DeeplTranslate\ExtendedVersionHelper;

class ExtendedVersionViewHelper extends AbstractViewHelper
{

    protected $escapeOutput = false;

    public function __construct(
        protected ExtendedVersionHelper $helper
    ){
    }

    public function render(): string
    {
        return $this->helper->render();
    }

}
