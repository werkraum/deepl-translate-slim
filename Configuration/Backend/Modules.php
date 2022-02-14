<?php

return [
    'deepl_translate_glossar' => [
        'parent' => 'site',
        'access' => 'user,group',
        'iconIdentifier' => 'deepl-translate-extension-icon',
        'labels' => 'LLL:EXT:deepl_translate/Resources/Private/Language/locallang_mod.xlf',
        'position' => ['after' => '*'],
        'extensionName' => 'deepl_translate',
        'controllerActions' => [
            \Werkraum\DeeplTranslate\Controller\GlossarController::class => [
                'status'
            ],
        ],
    ],
    'deepl_translate_cache_overview' => [
        'parent' => 'web_info',
        'access' => 'user,group',
        'path' => '/module/web/info/deeplCacheOverview',
        'labels' => [
            'title' => 'LLL:EXT:deepl_translate/Resources/Private/Language/locallang_tca.xlf:mod_tx_deepl_translate_deepl_translate_cache_overview',
        ],
        'routes' => [
            '_default' => [
                'target' => \Werkraum\DeeplTranslate\Controller\CacheOverviewController::class . '::handleRequest',
            ],
        ],
        'moduleData' => [
            'lang' => '',
            'depth' => 0,
        ],
    ]
];
