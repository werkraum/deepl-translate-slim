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
    'deepl_clear_all_cache' => [
        'path' => '/deepl/clear-all-cache',
        'target' => \Werkraum\DeeplTranslate\Controller\Backend\AjaxController::class . '::clearAllCache'
    ]
];
