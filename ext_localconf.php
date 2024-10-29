<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wr_deepl_translate') . '/Resources/Private/Libs/vendor/autoload.php';

//if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['wr_deepl_translate']['caching']['cacheConfigurations']['deepl_translate_cache'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['wr_deepl_translate']['caching']['cacheConfigurations']['deepl_translate_cache'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
        'options' => [
            'compression' => true,
            'defaultLifetime' => 0
        ]
    ];
//}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['wr_deepl_translate']
    = \Werkraum\DeeplTranslate\Hooks\DataHandlerHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['wr_deepl_translate']
    = \Werkraum\DeeplTranslate\Hooks\DataHandlerHook::class;

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] []= 'deepl';

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Werkraum']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
        \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
            'logFileInfix' => 'deepl'
        ]
    ]
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1717762840785] = [
    'nodeName' => 'extendedVersion',
    'priority' => 40,
    'class' => \Werkraum\DeeplTranslate\Backend\Form\Element\ExtendedVersionElement::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Configuration\SiteTcaConfiguration::class] = [
    'className' => \Werkraum\DeeplTranslate\ExtendedSiteTcaConfiguration::class,
];
