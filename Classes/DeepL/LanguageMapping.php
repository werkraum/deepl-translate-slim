<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */
namespace Werkraum\DeeplTranslate\DeepL;

class LanguageMapping
{
    public static function deepLToTYPO3(string $languageCode): string
    {
        switch ($languageCode) {
            case 'EN-GB':
                $languageCode = 'gb-eng';
                break;
            case 'EN-US':
                $languageCode = 'en-us-gb';
                break;
            case 'PT-BR':
                $languageCode = 'br';
                break;
            case 'PT-PT':
                $languageCode = 'pt';
                break;
            case 'CS':
                $languageCode = 'cz';
                break;
            case 'ZH':
                $languageCode = 'cn';
                break;
            case 'JA':
                $languageCode = 'jp';
                break;
            case 'EL':
                $languageCode = 'gr';
                break;
            case 'DA':
                $languageCode = 'dk';
                break;
            case 'NB':
                $languageCode = 'no';
                break;
            case 'UK':
                $languageCode = 'ua';
                break;
            case 'KO':
                $languageCode = 'kr';
                break;
            default:
                break;
        }
        return strtolower($languageCode);
    }

    public static function TYPO3ToDeepL(string $languageCode): string
    {
        switch ($languageCode) {
            case 'gb-eng':
                $languageCode = 'EN-GB';
                break;
            case 'en-us-gb':
                $languageCode = 'EN-US';
                break;
            case 'br':
                $languageCode = 'PT-BR';
                break;
            case 'pt':
                $languageCode = 'PT-PT';
                break;
            case 'cz':
                $languageCode = 'CS';
                break;
            case 'cn':
                $languageCode = 'ZH';
                break;
            case 'jp':
                $languageCode = 'JA';
                break;
            case 'gr':
                $languageCode = 'EL';
                break;
            case 'dk':
                $languageCode = 'DA';
                break;
            case 'no':
                $languageCode = 'NB';
                break;
            case 'ua':
                $languageCode = 'UK';
                break;
            case 'kr':
                $languageCode = 'KO';
                break;
            default:
                break;
        }
        return strtoupper($languageCode);
    }
}
