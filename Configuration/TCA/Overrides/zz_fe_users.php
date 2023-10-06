<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3') || die();

call_user_func(static function ($extKey = 'easy_verein') {

    $tca = [
        'ctrl' => [
            'searchFields' => $GLOBALS['TCA']['fe_users']['ctrl']['searchFields'] . ',easyverein_pk',
        ],
        'palettes' => [
            'welcome' => [
                'showitem' => 'welcome_mail,welcome_mail_sent'
            ],
        ],
        'types' => [
            0 => [
                'showitem' => str_replace('description,', 'description, easyverein_pk, --palette--;;welcome,', $GLOBALS['TCA']['fe_users']['types'][0]['showitem']),
            ],
        ],
        'columns' => [
            'easyverein_pk' => [
                'exclude' => true,
                'label' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:fe_users.easyverein_pk',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim',
                    'default' => '',
                    'max' => 255,
                    'readOnly' => true,
                ]
            ],
            'welcome_mail' => [
                'exclude' => true,
                'label' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:fe_users.welcome_mail',
                'config' => [
                    'type' => 'check',
                    'renderType' => 'checkboxToggle',
                    'items' => [
                        [
                            0 => '',
                            1 => '',
                        ]
                    ],
                    'default' => 0,
                ]
            ],
            'welcome_mail_sent' => [
                'exclude' => true,
                'label' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:fe_users.welcome_mail_sent',
                'config' => [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'size' => 16,
                    'eval' => 'datetime,int',
                    'readOnly' => true,
                ]
            ],
        ],
    ];
    $GLOBALS['TCA']['fe_users'] = array_replace_recursive($GLOBALS['TCA']['fe_users'], $tca);

});
