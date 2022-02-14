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

namespace Werkraum\DeeplTranslate\Middleware\EventListener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Werkraum\DeeplTranslate\Middleware\Event\IsTranslationAllowedEvent;
use Werkraum\DeeplTranslate\Site\Entity\SiteLanguage as DeeplSiteLanguage;

class IsAllowedLanguageEventListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __invoke(IsTranslationAllowedEvent $event): void
    {
        $request = $event->getRequest();
        /** @var Site $site */
        $site = $request->getAttribute('site', null);
        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('deepl_translate');

        /** @var SiteLanguage $originalLanguage */
        $originalLanguage = $request->getAttribute('language', $site->getDefaultLanguage());
        $requestedLanguage = strtoupper(trim((string) ($request->getParsedBody()['deepl'] ?? $request->getQueryParams()['deepl'] ?? null)));
        $disabled = DeeplSiteLanguage::getDeeplDisabled($originalLanguage);
        $allowedLanguages = DeeplSiteLanguage::getDeeplAllowedLanguages($originalLanguage) ?? (string)$site->getConfiguration()['default_deepl_allowed_languages'];

        if (empty($config['authenticationKey'])
            || $disabled
            || $allowedLanguages === ''
            || !in_array($requestedLanguage, explode(',', $allowedLanguages), true)
        ) {
            $event->setAllowed(false);
            $event->setStopPropagation(true);
        }
    }
}
