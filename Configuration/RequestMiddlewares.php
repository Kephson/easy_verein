<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
return [
    'frontend' => [
        'easyverein-api' => [
            'target' => \EHAERER\EasyVerein\Middleware\EasyVereinMiddleware::class,
            'before' => [
                'typo3/cms-redirects/redirecthandler',
            ],
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];
