<?php

require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('deepl_translate') . '/Resources/Private/Libs/vendor/autoload.php';

//if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['deepl_translate']['caching']['cacheConfigurations']['deepl_translate_cache'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['deepl_translate']['caching']['cacheConfigurations']['deepl_translate_cache'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
        'options' => [
            'compression' => true,
            'defaultLifetime' => 0
        ]
    ];
//}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['deepl_translate']
    = \Werkraum\DeeplTranslate\Hooks\DataHandlerHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['deepl_translate']
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
