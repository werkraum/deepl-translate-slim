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

namespace Werkraum\DeeplTranslate\DataProcessing;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\CanonicalizationUtility;
use Werkraum\DeeplTranslate\DeepL\DeepL;
use Werkraum\DeeplTranslate\DeepL\LanguageMapping;

class DeepLMenuProcessor implements DataProcessorInterface
{
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $authenticationKey = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('deepl_translate', 'authenticationKey');

        if (!empty($authenticationKey)) {
            $deepL = new DeepL($authenticationKey);
            $languages = $deepL->languages('target');
            $site = $this->getCurrentSite();
            $currentLanguage = $this->getCurrentLanguage();

            // [{"languageId":0,"locale":"en_US.UTF-8","title":"English","navigationTitle":"English","twoLetterIsoCode":"en","hreflang":"en-EN","direction":"","flag":"flags-en-us-gb","link":"\/","active":1,"current":0,"available":1},{"languageId":1,"locale":"de_DE","title":"Deutsch","navigationTitle":"Deutsch","twoLetterIsoCode":"de","hreflang":"de-DE","direction":"","flag":"flags-de","link":"\/de\/","active":0,"current":0,"available":1}]
            $menu = [];

            if ($site instanceof Site) {
                $targetSourceLanguage = \Werkraum\DeeplTranslate\Site\Entity\SiteLanguage::getDeeplSourceLanguage($currentLanguage) ?? (int)$site->getConfiguration()['default_deepl_source_language'];
                $allowedLanguages = \Werkraum\DeeplTranslate\Site\Entity\SiteLanguage::getDeeplAllowedLanguages($currentLanguage) ?? (string)$site->getConfiguration()['default_deepl_allowed_languages'];
                $disabled = \Werkraum\DeeplTranslate\Site\Entity\SiteLanguage::getDeeplDisabled($currentLanguage);

                if ($disabled || $allowedLanguages === '') {
                    return $processedData;
                }
                $allowedLanguages = explode(',', $allowedLanguages);
                foreach ($allowedLanguages as $language) {
                    $deepLDataKey = array_search($language, array_column($languages, 'language'));
                    $deepLData = $languages[$deepLDataKey];
                    $menu [] = [
                        'languageId' => $language,
                        'locale' => '',
                        'title' => $deepLData['name'],
                        'navigationTitle' => $deepLData['name'],
                        'twoLetterIsoCode' => '',
                        'hreflang' => $language,
                        'direction' => '',
                        'flag' => 'flags-' . LanguageMapping::deepLToTYPO3($language),
                        'link' => $this->getLink($language, $targetSourceLanguage),
                        'active' => false,
                        'current' => false,
                        'available' => true,
                    ];
                }
            }

            $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration);
            if (!($targetVariableName === false || $targetVariableName === 0 || $targetVariableName === '' || $targetVariableName === null)) {
                $processedData[$targetVariableName] = $menu;
            } else {
                $processedData['menu'] = $menu;
            }
        }

        return $processedData;
    }

    protected function getLink(string $language, int $sourceLanguage)
    {
        // Temporarily remove current mountpoint information as we want to have the
        // URL of the target page and not of the page within the mountpoint if the
        // current page is a mountpoint.
        $previousMp = $this->getTypoScriptFrontendController()->MP;
        $this->getTypoScriptFrontendController()->MP = '';

        $link = $this->getTypoScriptFrontendController()->cObj->typoLink_URL([
            'parameter' => $this->getTypoScriptFrontendController()->id . ',' . $this->getTypoScriptFrontendController()->getPageArguments()->getPageType(),
            'forceAbsoluteUrl' => true,
            'addQueryString' => true,
            'addQueryString.' => [
                'method' => 'GET',
                'exclude' => implode(
                    ',',
                    CanonicalizationUtility::getParamsToExcludeForCanonicalizedUrl(
                        $this->getTypoScriptFrontendController()->id,
                        (array)$GLOBALS['TYPO3_CONF_VARS']['FE']['additionalCanonicalizedUrlParameters']
                    )
                )
            ],
            'language' => $sourceLanguage,
            'additionalParams' => '&deepl=' . $language
        ]);
        $this->getTypoScriptFrontendController()->MP = $previousMp;
        return $link;
    }

    /**
     * Returns the currently configured "site" if a site is configured (= resolved) in the current request.
     */
    protected function getCurrentSite(): SiteInterface
    {
        try {
            return GeneralUtility::makeInstance(SiteFinder::class)
                ->getSiteByPageId($this->getTypoScriptFrontendController()->id);
        } catch (SiteNotFoundException) {
            return new NullSite();
        }
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    private function getCurrentLanguage(): SiteLanguage
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        if ($request->getAttribute('originalLanguage') instanceof SiteLanguage) {
            return $request->getAttribute('originalLanguage');
        }
        return $request->getAttribute('language');
    }
}
