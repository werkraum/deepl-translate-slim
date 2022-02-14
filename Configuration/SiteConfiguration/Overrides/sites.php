<?php

$ll = 'LLL:EXT:deepl_translate/Resources/Private/Language/locallang.xlf:';

$GLOBALS['SiteConfiguration']['site']['columns']['default_deepl_source_language'] = [
    'label' => $ll . 'site.default_deepl_source_language.label',
    'description' => $ll . 'site.default_deepl_source_language.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'itemsProcFunc' => \Werkraum\DeeplTranslate\UserFunc\SiteConfigurationItemProvider::class . '->getSysLanguage'
    ],
];

$GLOBALS['SiteConfiguration']['site']['columns']['default_deepl_allowed_languages'] = [
    'label' => $ll . 'site.default_deepl_allowed_languages.label',
    'description' => $ll . 'site.default_deepl_allowed_languages.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectCheckBox',
        'itemsProcFunc' => \Werkraum\DeeplTranslate\UserFunc\LanguageProvider::class . '->getLanguageOptions'
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_hreflang'] = [
    'label' => $ll . 'site.deepl_hreflang.label',
    'description' => $ll . 'site.deepl_hreflang.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'check',
        'default' => 0
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_exclude_elements_by_selector'] = [
    'label' => $ll . 'site.deepl_exclude_elements_by_selector.label',
    'description' => $ll . 'site.deepl_exclude_elements_by_selector.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'input',
        'default' => '.notranslate',
        'eval' => 'trim'
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_split_content_by_selectors'] = [
    'label' => $ll . 'site.deepl_split_content_by_selectors.label',
    'description' => $ll . 'site.deepl_split_content_by_selectors.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'user',
        'renderType' => 'extendedVersion',
        'default' => 0,
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_replace_links'] = [
    'label' => $ll . 'site.deepl_replace_links.label',
    'description' => $ll . 'site.deepl_replace_links.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'check'
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_replace_links_attribute'] = [
    'label' => $ll . 'site.deepl_replace_links_attribute.label',
    'description' => $ll . 'site.deepl_replace_links_attribute.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'input',
        'eval' => 'trim',
        'default' => 'hreflang'
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_translated_by_machine'] = [
    'label' => $ll . 'site.deepl_translated_by_machine.label',
    'description' => $ll . 'site.deepl_translated_by_machine.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'text',
        'enableRichtext' => true,
        'default' => 'This article has been machine-translated from {source} to the {target} language. As this article is not human translated, there may be chances of context, spelling and grammar mistakes. We recommend reading the original article: {originalLink}.'
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_translated_by_machine_target'] = [
    'label' => $ll . 'site.deepl_translated_by_machine_target.label',
    'description' => $ll . 'site.deepl_translated_by_machine_target.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'input',
        'default' => 'translated_by_machine'
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_translate_translated_by_machine'] = [
    'label' => $ll . 'site.deepl_translate_translated_by_machine.label',
    'description' => $ll . 'site.deepl_translate_translated_by_machine.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'check',
        'default' => true
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_recaptcha'] = [
    'label' => $ll . 'site.deepl_recaptcha.label',
    'description' => $ll . 'site.deepl_recaptcha.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'user',
        'renderType' => 'extendedVersion',
        'default' => 0,
    ],
];
$GLOBALS['SiteConfiguration']['site']['columns']['deepl_recaptcha_redirect_to_page'] = [
    'label' => $ll . 'site.deepl_recaptcha_redirect_to_page.label',
    'description' => $ll . 'site.deepl_recaptcha_redirect_to_page.description',
    'displayCond' => 'USER:Werkraum\DeeplTranslate\UserFunc\DisplayCond->authenticationKeyProvided',
    'config' => [
        'type' => 'user',
        'renderType' => 'extendedVersion',
        'default' => 0,
    ],
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',--div--;DeepL,default_deepl_source_language,default_deepl_allowed_languages,deepl_hreflang,deepl_exclude_elements_by_selector,deepl_split_content_by_selectors,deepl_replace_links,deepl_replace_links_attribute,deepl_translated_by_machine,deepl_translated_by_machine_target,deepl_translate_translated_by_machine,deepl_recaptcha,deepl_recaptcha_redirect_to_page';
