<?php

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
