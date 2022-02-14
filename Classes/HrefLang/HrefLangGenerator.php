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

namespace Werkraum\DeeplTranslate\HrefLang;

use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Event\ModifyHrefLangTagsEvent;

class HrefLangGenerator
{
    public function __construct(
        protected ContentObjectRenderer $cObj
    ){
    }

    public function __invoke(ModifyHrefLangTagsEvent $event): void
    {
        $hrefLangs = $event->getHrefLangs();
        if ((int)$this->getTypoScriptFrontendController()->page['no_index'] === 1) {
            return;
        }

        /** @var Site $site */
        $site = $event->getRequest()->getAttribute('site');
        $displayHreflang = (bool)$site->getConfiguration()['deepl_hreflang'];

        if (!$displayHreflang) {
            return;
        }

        $targetSourceLanguage = (int)$site->getConfiguration()['default_deepl_source_language'];
        $language = $site->getLanguageById($targetSourceLanguage);

        $hreflang = $language->getHreflang();

        $baseUrl = $hrefLangs[$hreflang];

        if (empty($baseUrl)) {
            // possibly a page with a canonical url ... nothing to do
            return;
        }

        $allowedLanguages = (string)$site->getConfiguration()['default_deepl_allowed_languages'];
        foreach (explode(',', $allowedLanguages) as $languageCode) {
            // lowercase is important since it describes the language not the region https://en.wikipedia.org/wiki/ISO_639-1
            $languageCode = strtolower($languageCode);
            if (!isset($hrefLangs[$languageCode])) {
                $uri = new Uri($baseUrl);
                $hrefLangs[$languageCode] = (string)$uri->withQuery('deepl=' . $languageCode);
            }
        }

        $event->setHrefLangs($hrefLangs);
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }
}
