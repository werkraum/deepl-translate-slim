<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Hooks;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Werkraum\DeeplTranslate\Cache\DeeplCacheManager;

class DataHandlerHook
{

    /**
     * @param string $action The action to perform, e.g. 'update'.
     * @param string $table The table affected by action, e.g. 'fe_users'.
     * @param int $uid The uid of the record affected by action.
     * @param array $modifiedFields The modified fields of the record.
     */
    public function processDatamap_postProcessFieldArray(
        $action,
        $table,
        $uid,
        array &$modifiedFields,
        DataHandler $dataHandler
    ): void {
        $clearCache = (bool)GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('wr_deepl_translate', 'clearCache');

        if (false === $clearCache) {
            return;
        }

        if ($action === 'update' && $table === 'pages') {
            $this->clearDeeplCacheForPage($uid);
        }
        if ($action === 'update' && $table === 'tt_content') {
            $pid = $dataHandler->getPID($table, $uid);
            $this->clearDeeplCacheForPage($pid);
        }
        if ($action === 'new' && $table === 'tt_content') {
            $pid = $modifiedFields['pid'];
            $this->clearDeeplCacheForPage($pid);
        }
    }

    private function clearDeeplCacheForPage(int $page): void
    {
        GeneralUtility::makeInstance(DeeplCacheManager::class)
            ->flushCachesByTag("deeplPageId_$page");
    }
}
