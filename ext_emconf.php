<?php /** @noinspection PhpUndefinedVariableInspection */

/* * *************************************************************
 * Extension Manager/Repository config file for ext "easy_verein".
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 * ************************************************************* */

$EM_CONF[$_EXTKEY] = [
    'title' => 'TYPO3 <=> EasyVerein connector',
    'description' => 'Extension to connect TYPO3 with easyVerein association management software',
    'category' => 'plugin',
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state' => 'stable',
    'author' => 'Ephraim Härer',
    'author_email' => 'mail@ephra.im',
    'author_company' => 'private',
    'autoload' => [
        'psr-4' => [
            'EHAERER\\EasyVerein\\' => 'Classes'
        ]
    ],
];

