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

namespace Werkraum\DeeplTranslate;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Fluid\View\StandaloneView;

#[Autoconfigure(public: true)]
class ExtendedVersionHelper
{

    public function __construct(
        protected StandaloneView $standaloneView
    ) {
        $this->standaloneView->setTemplateRootPaths(['EXT:wr_deepl_translate/Resources/Private/Templates/']);
    }

    public function render(): string
    {
        return $this->standaloneView->render('ExtendedVersion');
    }

}
