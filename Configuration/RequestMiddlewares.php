<?php

use EHAERER\EasyVerein\Middleware\EasyVereinMiddleware;

return [
    'frontend' => [
        'easyverein-api' => [
            'target' => EasyVereinMiddleware::class,
            'before' => [
                'typo3/cms-redirects/redirecthandler',
            ],
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];
