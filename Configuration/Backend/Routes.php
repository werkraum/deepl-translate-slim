<?php

return [
    'deepl_clear_all_cache' => [
        'path' => '/deepl/clear-all-cache',
        'target' => \Werkraum\DeeplTranslate\Controller\Backend\AjaxController::class . '::clearAllCache'
    ]
];
