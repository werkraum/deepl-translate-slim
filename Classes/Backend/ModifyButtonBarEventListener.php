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
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ModifyButtonBarEventListener
{

    private BackendUserAuthentication $backendUser;

    public function __construct(
        protected IconFactory $iconFactory,
        protected PageRenderer $pageRenderer,
        protected UriBuilder $uriBuilder
    ) {
        $this->backendUser = $GLOBALS['BE_USER'];
    }

    public function __invoke(\TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent $event): void
    {
        $buttons = $event->getButtons();
        $buttonBar = $event->getButtonBar();

        $pageId = (int)($GLOBALS['TYPO3_REQUEST']->getParsedBody()['id'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['id'] ?? null);

        $isAdmin = $this->backendUser->isAdmin();
        $userTsConfig = $this->backendUser->getTSConfig();

        if (($pageId > 0 && $isAdmin) || ($userTsConfig['options.']['clearCache.']['deepl'] ?? false)) {
            $button = $buttonBar->makeLinkButton();
            $button->setDataAttributes(['id' => $pageId]);
            $button->setClasses('deepl-clear-page-cache');
            $button->setHref('#');
            $button->setIcon($this->iconFactory->getIcon('actions-deepl-cache-clear', Icon::SIZE_SMALL));
            $button->setTitle($this->getLanguageService()->sL('LLL:EXT:wr_deepl_translate/Resources/Private/Language/locallang.xlf:clearPageCacheTitle'));
            $buttons[ButtonBar::BUTTON_POSITION_RIGHT][0][] = $button;

            /** @var PageRenderer $pageRenderer */
            $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/DeeplTranslate/ClearCache');
            $event->setButtons($buttons);
        }

    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

}
