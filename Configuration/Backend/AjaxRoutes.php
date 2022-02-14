<?php

return [
    'deepl_clear_page_cache' => [
        'path' => '/deepl/clear-page-cache',
        'target' => \Werkraum\DeeplTranslate\Controller\Backend\AjaxController::class . '::clearPageCache'
    ]
];
