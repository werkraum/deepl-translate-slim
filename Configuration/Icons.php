<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

declare(strict_types=1);

return [
    'actions-deepl-cache-clear' => [
        'source' => 'EXT:wr_deepl_translate/Resources/Public/Icons/actions-bolt-deepl.svg',
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ],
    'actions-deepl-cache-clear-red' => [
        'source' => 'EXT:wr_deepl_translate/Resources/Public/Icons/actions-bolt-deepl-red.svg',
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ],
    'deepl-translate-extension-icon' => [
        'source' => 'EXT:wr_deepl_translate/Resources/Public/Icons/Extension.svg',
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ],
];
