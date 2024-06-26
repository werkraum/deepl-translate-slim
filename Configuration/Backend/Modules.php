<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

return [
    'wr_deepl_translate_glossar' => [
        'parent' => 'site',
        'access' => 'user,group',
        'iconIdentifier' => 'deepl-translate-extension-icon',
        'labels' => 'LLL:EXT:wr_deepl_translate/Resources/Private/Language/locallang_mod.xlf',
        'position' => ['after' => '*'],
        'extensionName' => 'wr_deepl_translate',
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
            'title' => 'LLL:EXT:wr_deepl_translate/Resources/Private/Language/locallang_tca.xlf:mod_tx_deepl_translate_deepl_translate_cache_overview',
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
