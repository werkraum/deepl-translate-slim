<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Backend;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class ModifyClearCacheActionsEventListener
{

    private BackendUserAuthentication $backendUser;

    public function __construct(
        protected UriBuilder $uriBuilder
    )
    {
        $this->backendUser = $GLOBALS['BE_USER'];
    }

    public function __invoke(\TYPO3\CMS\Backend\Backend\Event\ModifyClearCacheActionsEvent $event): void
    {
        $isAdmin = $this->backendUser->isAdmin();
        $userTsConfig = $this->backendUser->getTSConfig();

        if ($isAdmin || $userTsConfig['options.']['clearCache.']['deepl'] ?? false) {
            $cacheAction = [
                'id' => 'clearDeeplTranslationCache',
                'title' => 'LLL:EXT:wr_deepl_translate/Resources/Private/Language/locallang.xlf:clearAllCacheTitle',
                'description' => 'LLL:EXT:wr_deepl_translate/Resources/Private/Language/locallang.xlf:clearAllCacheDescription',
                'href' => (string)$this->uriBuilder->buildUriFromRoute('deepl_clear_all_cache'),
                'iconIdentifier' => 'actions-deepl-cache-clear-red'
            ];
            $event->addCacheAction($cacheAction);
            $event->addCacheActionIdentifier('deepl');
        }
    }

}
