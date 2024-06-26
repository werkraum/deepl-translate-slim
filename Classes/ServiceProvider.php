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

namespace  Werkraum\DeeplTranslate;

use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Package\AbstractServiceProvider;
use Werkraum\DeeplTranslate\Cache\DeeplCacheManager;

class ServiceProvider extends AbstractServiceProvider
{

    protected static function getPackagePath(): string
    {
        return __DIR__ . '/../';
    }

    protected static function getPackageName(): string
    {
        return 'werkraum/deepl-translate-slim';
    }

    public function getFactories(): array
    {
        return [
            Cache\DeeplCacheManager::class => [static::class, 'getCacheManager']
        ];
    }

    public static function getCacheManager(ContainerInterface $container): DeeplCacheManager
    {
        if (!$container->get('boot.state')->complete) {
            throw new \LogicException(DeeplCacheManager::class . ' can not be injected/instantiated during ext_localconf.php loading. Use lazy loading instead.', 1_549_446_998);
        }

        $cacheConfigurations = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['wr_deepl_translate']['caching']['cacheConfigurations'] ?? [];
        $disableCaching = $container->get('boot.state')->cacheDisabled;

        $cacheManager = self::new($container, DeeplCacheManager::class, [$disableCaching]);
        $cacheManager->setCacheConfigurations($cacheConfigurations);

        return $cacheManager;
    }

}
