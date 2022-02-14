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

namespace Werkraum\DeeplTranslate\Site\Entity;

class SiteLanguage
{

    /**
     * @return string|null
     */
    public static function getDeeplAllowedLanguages(\TYPO3\CMS\Core\Site\Entity\SiteLanguage $siteLanguage)
    {
        $configuration = $siteLanguage->toArray();
        if (!empty($configuration['deepl_allowed_languages'])) {
            $deeplAllowedLanguages = $configuration['deepl_allowed_languages'];
            // It is important to distinct between "0" and "" so, empty() should not be used here
            if (is_array($deeplAllowedLanguages)) {
                return implode(',', $deeplAllowedLanguages);
            }
            return $deeplAllowedLanguages;
        }
        return null;
    }

    public static function getDeeplSourceLanguage(\TYPO3\CMS\Core\Site\Entity\SiteLanguage $siteLanguage): ?int
    {
        $configuration = $siteLanguage->toArray();
        if (isset($configuration['deepl_source_language'])) {
            $language = $configuration['deepl_source_language'];
            return (int)$language;
        }
        return null;
    }

    public static function getDeeplDisabled(\TYPO3\CMS\Core\Site\Entity\SiteLanguage $siteLanguage): bool
    {
        $configuration = $siteLanguage->toArray();
        if (isset($configuration['deepl_disabled'])) {
            $disabled = $configuration['deepl_disabled'];
            return (bool) $disabled;
        }
        return false;
    }
}
