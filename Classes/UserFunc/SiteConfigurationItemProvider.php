<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\UserFunc;

use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteConfigurationItemProvider
{

    public function getSysLanguage(array &$fieldDefinition): void
    {
        foreach (GeneralUtility::makeInstance(SiteFinder::class)->getAllSites() as $site) {
            foreach ($site->getAllLanguages() as $languageId => $language) {
                if (!isset($fieldDefinition['items'][$languageId])) {
                    $fieldDefinition['items'][$languageId] = [
                        'label' => $language->getTitle(),
                        'value' => $languageId,
                        'icon' => $language->getFlagIdentifier(),
                        'tempTitles' => [],
                    ];
                } elseif ($fieldDefinition['items'][$languageId]['label'] !== $language->getTitle()) {
                    // Temporarily store different titles
                    $fieldDefinition['items'][$languageId]['tempTitles'][] = $language->getTitle();
                }
            }
        }

        if (!isset($fieldDefinition['items'][0])) {
            // Since TcaSiteLanguage has a special behaviour, enforcing the
            // default language ("0") to be always added to the site configuration,
            // we have to add it to the available items, in case it is not already
            // present. This only happens for the first ever created site configuration.
            $fieldDefinition['items'][] = ['label' => 'Default', 'value' => 0, 'icon' => '', 'tempTitles' => []];
        }

        ksort($fieldDefinition['items']);

        // Build the final language label
        foreach ($fieldDefinition['items'] as &$language) {
            if ($language instanceof SelectItem) {
                continue;
            }
            $language['label'] .= ' [' . $language['value'] . ']';
            if ($language['tempTitles'] !== []) {
                $language['label'] .= ' (' . implode(',', array_unique($language['tempTitles'])) . ')';
                // Unset the temporary title "storage"
                unset($language['tempTitles']);
            }
        }
        unset($language);

    }

}
