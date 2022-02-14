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

namespace Werkraum\DeeplTranslate\UserFunc;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Werkraum\DeeplTranslate\DeepL\DeepL;
use Werkraum\DeeplTranslate\DeepL\LanguageMapping;

class LanguageProvider implements SingletonInterface
{
    protected $options = [];

    public function __construct()
    {
        $authenticationKey = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('deepl_translate', 'authenticationKey');

        if (!empty($authenticationKey)) {
            $deepL = new DeepL($authenticationKey);
            $this->options = $deepL->languages('target');
        }
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getLanguageOptions(array &$configuration): void
    {
        foreach ($this->options as $language) {
            $configuration['items'][] = [
                $language['name'],
                $language['language'],
                $this->getFlagIcon($language['language'])
            ];
        }
    }

    private function getFlagIcon(string $deeplLanguageCode): string
    {
        $iconPrefix = 'flags-';
        return $iconPrefix . LanguageMapping::deepLToTYPO3($deeplLanguageCode);
    }
}
