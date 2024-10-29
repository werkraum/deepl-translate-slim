<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

return [
    'frontend' => [
        'werkraum/deepl-translator' => [
            'target' => \Werkraum\DeeplTranslate\Middleware\TranslationMiddleware::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/tsfe'
            ]
        ],
    ],
];
