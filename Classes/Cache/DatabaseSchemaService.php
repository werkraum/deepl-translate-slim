<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Cache;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

final class DatabaseSchemaService
{

    /**
     * An event listener to inject the required caching framework database tables to the
     * tables definitions string
     */
    public function addCachingFrameworkDatabaseSchema(AlterTableDefinitionStatementsEvent $event): void
    {
        $event->addSqlData($this->getCachingFrameworkRequiredDatabaseSchema());
    }

    /**
     * Get schema SQL of required cache framework tables.
     *
     * This method needs ext_localconf and ext_tables loaded!
     *
     * @return string Cache framework SQL
     */
    private function getCachingFrameworkRequiredDatabaseSchema()
    {
        // Use new to circumvent the singleton pattern of CacheManager
        $cacheManager = new CacheManager();
        $cacheManager->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['wr_deepl_translate']['caching']['cacheConfigurations']);

        $tableDefinitions = '';
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['wr_deepl_translate']['caching']['cacheConfigurations'] as $cacheName => $_) {
            $backend = $cacheManager->getCache($cacheName)->getBackend();
            if (method_exists($backend, 'getTableDefinitions')) {
                $tableDefinitions .= LF . $backend->getTableDefinitions();
            }
        }

        return $tableDefinitions;
    }
}
