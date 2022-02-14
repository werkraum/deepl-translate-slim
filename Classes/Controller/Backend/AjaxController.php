<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Controller\Backend;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Werkraum\DeeplTranslate\Cache\DeeplCacheManager;

class AjaxController
{
    private DeeplCacheManager $cacheManager;

    /**
     * @param DeeplCacheManager $cacheManager
     */
    public function __construct()
    {
        $this->cacheManager = GeneralUtility::makeInstance(DeeplCacheManager::class);
    }

    public function clearPageCache(ServerRequestInterface $request): JsonResponse
    {
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $pageUid = (int)($parsedBody['id'] ?? $queryParams['id'] ?? 0);
        $message = $this->getLanguageService()->sL('LLL:EXT:deepl_translate/Resources/Private/Language/locallang.xlf:clearedPageCache.message.error');
        $success = false;
        $permissionClause = $this->getBackendUserAuthentication()->getPagePermsClause(Permission::PAGE_SHOW);
        $pageRow = BackendUtility::readPageAccess($pageUid, $permissionClause);
        if ($pageUid !== 0 && $this->getBackendUserAuthentication()->doesUserHaveAccess($pageRow, Permission::PAGE_SHOW)) {
            $this->cacheManager->flushCachesByTag('deeplPageId_' . $pageUid);
            $message = $this->getLanguageService()->sL('LLL:EXT:deepl_translate/Resources/Private/Language/locallang.xlf:clearedPageCache.message.success');
            $success = true;
        }
        return new JsonResponse([
            'success' => $success,
            'title' => $this->getLanguageService()->sL('LLL:EXT:deepl_translate/Resources/Private/Language/locallang.xlf:clearedPageCache.title'),
            'message' => $message
        ]);
    }

    public function clearAllCache(ServerRequestInterface $request): HtmlResponse
    {
        $this->cacheManager->flushCachesByTag('deepl_translations');
        return new HtmlResponse('');
    }

    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
