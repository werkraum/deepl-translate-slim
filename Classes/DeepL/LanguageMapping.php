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
        $flagMap = [
            // English & Portuguese Variants
            'EN-GB'   => 'gb',
            'EN-US'   => 'us',     // 'us' is the standard TYPO3 core flag for US English
            'PT-BR'   => 'br',
            'PT-PT'   => 'pt',
            // Asian & Middle Eastern Languages
            'AR'      => 'arab',
            'JA'      => 'jp',
            'KO'      => 'kr',
            'ZH'      => 'cn',
            'ZH-HANS' => 'cn',
            'ZH-HANT' => 'tw',
            'HE'      => 'il',     // Hebrew
            'FA'      => 'ir',     // Persian
            'MY'      => 'mm',     // Burmese
            // Indian Subcontinent Languages
            'HI'      => 'in',
            'AS'      => 'in',     // Assamese
            'BN'      => 'bd',     // Bengali
            'GU'      => 'in',     // Gujarati
            'ML'      => 'in',     // Malayalam
            'MR'      => 'in',     // Marathi
            'PA'      => 'in',     // Punjabi
            'SA'      => 'in',     // Sanskrit
            'TA'      => 'in',     // Tamil
            'TE'      => 'in',     // Telugu
            'UR'      => 'pk',     // Urdu
            // European Languages & Regional Flags
            'BE'      => 'by',     // Belarusian
            'CA'      => 'es',     // Catalan -> Maps to Spain (es)
            'CS'      => 'cz',     // Czech
            'CY'      => 'gb',     // Welsh -> Maps to United Kingdom (gb)
            'DA'      => 'dk',     // Danish
            'EL'      => 'gr',     // Greek
            'GA'      => 'ie',     // Irish
            'LB'      => 'lu',     // Luxembourgish
            'NB'      => 'no',     // Norwegian Bokmål
            'SL'      => 'si',     // Slovenian
            'SQ'      => 'al',     // Albanian
            'SR'      => 'rs',     // Serbian
            'SV'      => 'se',     // Swedish
            'UK'      => 'ua',     // Ukrainian
            // African Languages
            'AF'      => 'za',     // Afrikaans
            'HA'      => 'ng',     // Hausa
            'IG'      => 'ng',     // Igbo
            'LN'      => 'cd',     // Lingala
            'OM'      => 'et',     // Oromo
            'ST'      => 'ls',     // Sesotho
            'SW'      => 'ke',     // Swahili
            'TN'      => 'bw',     // Tswana
            'TS'      => 'za',     // Tsonga
            'WO'      => 'sn',     // Wolof
            'XH'      => 'za',     // Xhosa
            'ZU'      => 'za',     // Zulu
            // Latin American & Other Languages
            'AY'      => 'bo',     // Aymara
            'ES-419'  => 'mx',     // Latin American Spanish
            'GN'      => 'py',     // Guarani
            'HT'      => 'ht',     // Haitian Creole
            'QU'      => 'pe',     // Quechua
            // Rest of the mismatched codes mapped to their primary countries
            'AN'      => 'es',     // Aragonese
            'BA'      => 'ru',     // Bashkir
            'BR'      => 'fr',     // Breton
            'BS'      => 'ba',     // Bosnian
            'ET'      => 'ee',     // Estonian
            'EU'      => 'es',     // Basque
            'GL'      => 'es',     // Galician
            'HY'      => 'am',     // Armenian
            'JV'      => 'id',     // Javanese
            'KA'      => 'ge',     // Georgian
            'KK'      => 'kz',     // Kazakh
            'KY'      => 'kg',     // Kyrgyz
            'LA'      => 'va',     // Latin
            'MI'      => 'nz',     // Maori
            'MS'      => 'my',     // Malay
            'NE'      => 'np',     // Nepali
            'OC'      => 'fr',     // Occitan
            'PS'      => 'af',     // Pashto
            'SU'      => 'id',     // Sundanese
            'TG'      => 'tj',     // Tajik
            'TK'      => 'tm',     // Turkmen
            'TL'      => 'ph',     // Tagalog
            'TT'      => 'ru',     // Tatar
            'VI'      => 'vn',     // Vietnamese
            'YI'      => 'il',     // Yiddish
            // Regional & Constructed Languages (Non-ISO 3166-1)
            'EO'      => 'multiple', // Esperanto -> Maps to TYPO3's default multi-language globe/rainbow flag
        ];
        return $flagMap[$languageCode] ?? strtolower($languageCode);
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
