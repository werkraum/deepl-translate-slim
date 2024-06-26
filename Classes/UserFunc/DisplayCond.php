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

namespace Werkraum\DeeplTranslate\UserFunc;

use TYPO3\CMS\Backend\Form\FormDataProvider\EvaluateDisplayConditions;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DisplayCond implements SingletonInterface
{
    protected bool $isValid;

    public function __construct()
    {
        $authenticationKey = GeneralUtility::makeInstance(ExtensionConfiguration::class)
        ->get('wr_deepl_translate', 'authenticationKey');
        $this->isValid = !empty($authenticationKey);
    }

    public function authenticationKeyProvided(array $params, EvaluateDisplayConditions $evaluateDisplayConditions)
    {
        return $this->isValid;
    }
}
