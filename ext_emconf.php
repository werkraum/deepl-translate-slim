<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'DeepL Translate Slim',
    'description' => 'Automatic translations of entire TYPO3 websites with DeepL. Quick and easy. Without extra languages in the backend and without headaches.',
    'category' => 'plugin',
    'version' => '1.1.1',
    'author' => 'Lukas Niestroj',
    'author_email' => 'lukas.niestroj@werkraum.net',
    'author_company' => 'werkraum.net',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
