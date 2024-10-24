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
 * This file is part of TYPO3 CMS-based extension "wr_deepl_translate" by werkraum.
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

namespace Werkraum\DeeplTranslate\DocumentProcessor\Processor;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use Werkraum\DeeplTranslate\DeepL\DeepL;
use Werkraum\DeeplTranslate\DocumentProcessor\DocumentProcessorInterface;

class TranslateByMachineProcessor implements DocumentProcessorInterface
{
    private string $text;

    public function extractFromDocument(\DOMDocument $document): void
    {
        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site', null);
        /** @var SiteLanguage $originalLanguage */
        $originalLanguage = $GLOBALS['TYPO3_REQUEST']->getAttribute('language', $site->getDefaultLanguage());
        $requestedLanguage = strtoupper(trim((string) ($GLOBALS['TYPO3_REQUEST']->getParsedBody()['deepl'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['deepl'] ?? null)));
        $targetSourceLanguage = \Werkraum\DeeplTranslate\Site\Entity\SiteLanguage::getDeeplSourceLanguage($originalLanguage) ?? (int)$site->getConfiguration()['default_deepl_source_language'];

        if ($originalLanguage->getLanguageId() !== $targetSourceLanguage) {
            $language = $site->getLanguageById($targetSourceLanguage);
        } else {
            $language = $originalLanguage;
        }

        $deepL = new DeepL();
        $currentUri = $GLOBALS['TYPO3_REQUEST']->getUri();

        $translatedByMachineText = (string)$site->getConfiguration()['deepl_translated_by_machine'];
        $translatedByMachineText = \str_replace('originalLink', 'originalLink -> f:format.raw()', $translatedByMachineText);

        $languageData = $deepL->languageData($requestedLanguage);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateSource('<f:format.raw><f:spaceless>' . $translatedByMachineText . '</f:spaceless></f:format.raw>');
        $view->assignMultiple([
            'source' => $language->getNavigationTitle(),
            'target' => $languageData['name'],
            'originalLink' => "<a target='_parent' hreflang='{$language->getHreflang()}' href='{$currentUri->withQuery('')->__toString()}'>{$language->getTitle()} version</a>"
        ]);

        $this->text = $view->render();
    }

    public function getTextsForTranslation(): array
    {
        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site', null);
        $translateTranslatedByMachineText = (bool)$site->getConfiguration()['deepl_translate_translated_by_machine'];
        if ($translateTranslatedByMachineText) {
            return [$this->text];
        }

        return [];
    }

    public function setTranslations(array $texts): void
    {
        if (isset($texts[0])) {
            $this->text = $texts[0];
        }
    }

    public function embedInDocument(\DOMDocument $document): void
    {
        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site', null);
        $translatedByMachineTarget = (string)$site->getConfiguration()['deepl_translated_by_machine_target'];

        $xpath = new \DOMXPath($document);
        $target = $xpath->query("//*[@id='$translatedByMachineTarget']")->item(0);
        if ($target instanceof \DOMElement) {
            $tempDoc = new \DOMDocument('1.0', 'UTF-8');
            @$tempDoc->loadHTML(mb_convert_encoding($this->text, 'HTML-ENTITIES', 'UTF-8'));
            $tempElement = $tempDoc->documentElement;
            $tempNode = $document->importNode($tempElement, true);

            $target->appendChild($tempNode);
        }
    }

    public function sendMultipleTranslationRequests(): bool
    {
        return false;
    }

    public static function getPriority(): int
    {
        return 10;
    }
}
