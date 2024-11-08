<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Info\Controller\TranslationStatusController;
use Werkraum\DeeplTranslate\Cache\DeeplCacheManager;
use Werkraum\DeeplTranslate\UserFunc\LanguageProvider;

class CacheOverviewController extends TranslationStatusController
{

    /**
     * @var array
     */
    protected $deeplLanguages = [];

    protected string $currentLanguage = "";

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->init($request);
        $this->initializeDeeplLanguages($request);
        $backendUser = $this->getBackendUser();
        $moduleData = $request->getAttribute('moduleData');
        $allowedModuleOptions = $this->getAllowedModuleOptions();
        if ($moduleData->cleanUp($allowedModuleOptions)) {
            $backendUser->pushModuleData($moduleData->getModuleIdentifier(), $moduleData->toArray());
        }

        $this->currentDepth = (int)$moduleData->get('depth');
        $this->currentLanguage = $moduleData->get('lang');

        if ($this->id) {
            $tree = $this->getTree();
            $content = $this->renderL10nTable($tree, $request);
            $this->view->assignMultiple([
                'pageUid' => $this->id,
                'depthDropdownOptions' => $allowedModuleOptions['depth'],
                'depthDropdownCurrentValue' => $this->currentDepth,
                'content' => $content,
            ]);
        }

        return $this->view->renderResponse('DeeplCacheOverview');
    }

    protected function getAllowedModuleOptions(): array
    {
        $lang = $this->getLanguageService();
        $menuArray = [
            'depth' => [
                0 => $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.depth_0'),
                1 => $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.depth_1'),
                2 => $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.depth_2'),
                3 => $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.depth_3'),
                4 => $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.depth_4'),
                999 => $lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.depth_infi'),
            ],
        ];
        // Languages:
        $menuArray['lang'] = [];
        foreach ($this->deeplLanguages as $language) {
            $menuArray['lang'][$language] = $language;
        }
        return $menuArray;
    }

    private function initializeDeeplLanguages(ServerRequestInterface $request): void
    {
        /** @var SiteInterface $currentSite */
        $currentSite = $request->getAttribute('site');

        if ($currentSite instanceof NullSite) {
            $this->deeplLanguages = [''];
            return;
        }

        $allowedLanguages = (string)$currentSite->getConfiguration()['default_deepl_allowed_languages'];
        if ($allowedLanguages !== '') {
            $this->deeplLanguages = explode(',', $allowedLanguages);
        }
    }

    protected function renderL10nTable(PageTreeView $tree, ServerRequestInterface $request): string
    {
        $lang = $this->getLanguageService();
        $backendUser = $this->getBackendUser();
        // Title length:
        $titleLen = (int)$backendUser->uc['titleLen'];
        // Put together the TREE:
        $output = '';
        $langRecUids = [];

        $userTsConfig = $backendUser->getTSConfig();
        $showPageId = !empty($userTsConfig['options.']['pageTree.']['showPageIdWithTitle']);

        $languageMapping = $this->getLanguageProvider()->getOptions();
        $cacheManager = $this->getCacheManager();

        // If another page module was specified, replace the default Page module with the new one
        $pageModule = trim($userTsConfig['options.']['overridePageModule'] ?? '');
        $pageModule = $this->moduleProvider->isModuleRegistered($pageModule) ? $pageModule : 'web_layout';
        $pageModuleAccess = $this->moduleProvider->accessGranted($pageModule, $backendUser);

        // body
        foreach ($tree->tree as $data) {
            $tCells = [];
            $langRecUids[0][] = $data['row']['uid'];
            $pageTitle = ($showPageId ? '[' . (int)$data['row']['uid'] . '] ' : '') . GeneralUtility::fixed_lgd_cs($data['row']['title'], $titleLen);
            // Page icons / titles etc.
            if ($pageModuleAccess) {
                $pageModuleLink = (string)$this->uriBuilder->buildUriFromRoute($pageModule, ['id' => $data['row']['uid'], 'SET' => ['language' => 0]]);
                $pageModuleLink = '<a href="' . htmlspecialchars($pageModuleLink) . '" title="' . $lang->sL('LLL:EXT:info/Resources/Private/Language/locallang_webinfo.xlf:lang_renderl10n_editPage') . '">' . htmlspecialchars($pageTitle) . '</a>';
            } else {
                $pageModuleLink = htmlspecialchars($pageTitle);
            }
            $icon = '<span title="' . BackendUtility::getRecordIconAltText($data['row'], 'pages') . '">'
                . $this->iconFactory->getIconForRecord('pages', $data['row'], Icon::SIZE_SMALL)->setTitle(BackendUtility::getRecordIconAltText($data['row'], 'pages', false))->render()
                . '</span>';
            if ($this->getBackendUser()->recordEditAccessInternals('pages', $data['row'])) {
                $icon = BackendUtility::wrapClickMenuOnIcon($icon, 'pages', $data['row']['uid']);
            }

            $tCells[] = '<td' . (!empty($data['row']['_CSSCLASS']) ? ' class="' . $data['row']['_CSSCLASS'] . '"' : '') . '>' .
                (!empty($data['depthData']) ? $data['depthData'] : '') .
                ($data['HTML'] ?? '') .
                $icon .
                $pageModuleLink .
                ((string)$data['row']['nav_title'] !== '' ? ' [Nav: <em>' . htmlspecialchars(GeneralUtility::fixed_lgd_cs($data['row']['nav_title'], $titleLen)) . '</em>]' : '') .
                '</td>';

            foreach ($this->deeplLanguages as $language) {
                $backend = $cacheManager->getCache('deepl_translate_cache')->getBackend();
                $content = [];
                if ($backend instanceof TaggableBackendInterface) {
                    $tag = 'deeplPage';
                    $tag .= '_' . (int)$data['row']['uid'] . '_';
                    $tag .= $language;
                    $ids = $backend->findIdentifiersByTag($tag);

                    foreach ($ids as $identifier) {
                        if ($backend instanceof Typo3DatabaseBackend) {
                            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                                ->getQueryBuilderForTable($backend->getCacheTable());
                            $cacheRow = $queryBuilder->select('expires')
                                ->from($backend->getCacheTable())
                                ->where(
                                    $queryBuilder->expr()
                                        ->eq('identifier', $queryBuilder->createNamedParameter($identifier)),
                                    $queryBuilder->expr()
                                        ->gte('expires', $queryBuilder->createNamedParameter(
                                            GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp')
                                        )
                                ))
                                ->executeQuery()
                                ->fetchAssociative();
                            if (!empty($cacheRow)) {
                                $content []= (new \DateTime('@' . $cacheRow['expires']))->format('d.m.Y H:i') . ' ' . \substr((string) $identifier, 0, 5) . '[...]';
                            }
                        } else {
                            $content []= substr((string) $identifier, 0, 5) . '[...]';
                        }
                    }
                }

                $tCells []= '<td class="col-border-left">' . \implode("\n", $content) . '</td>';
            }

            $output .= '
				<tr>
					' . implode('
					', $tCells) . '
				</tr>';
        }

        // header
        $tCells = [];
        $tCells[] = '<th style="min-width: 200px">' . $lang->sL('LLL:EXT:info/Resources/Private/Language/locallang_webinfo.xlf:lang_renderl10n_page') . '</th>';
        foreach ($this->deeplLanguages as $language) {
            $label = $language;
            foreach ($languageMapping as $item) {
                if ($language === $item['language']) {
                    $label .= ' - ';
                    $label .= $item['name'];
                    break;
                }
            }
            $tCells []= '<th class="col-border-left">' . $label . '</th>';
        }
        return '<div class="table-fit"><table class="table table-striped table-hover" id="langTable"><thead><tr>' .
        implode('', $tCells) .
        '</tr>' .
        '</thead>' .
        '<tbody>' .
        $output .
        '</tbody>' .
        '</table>' .
        '</div>';
    }

    protected function getLanguageProvider(): LanguageProvider
    {
        return GeneralUtility::makeInstance(LanguageProvider::class);
    }

    protected function getCacheManager(): DeeplCacheManager
    {
        return GeneralUtility::makeInstance(DeeplCacheManager::class);
    }
}
