<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3') || die();

call_user_func(static function ($extKey = 'easy_verein') {

    $tca = [
        'ctrl' => [
            'searchFields' => $GLOBALS['TCA']['fe_groups']['ctrl']['searchFields'] . ',easyverein_g_short',
        ],
        'types' => [
            0 => [
                'showitem' => str_replace('tx_extbase_type', 'tx_extbase_type, easyverein_g_short,', $GLOBALS['TCA']['fe_groups']['types'][0]['showitem']),
            ],
        ],
        'columns' => [
            'easyverein_g_short' => [
                'exclude' => true,
                'label' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:fe_groups.easyverein_g_short',
                'config' => [
                    'type' => 'input',
                    'size' => 10,
                    'eval' => 'trim',
                    'default' => '',
                    'max' => 10,
                ]
            ],
        ],
    ];
    $GLOBALS['TCA']['fe_groups'] = array_replace_recursive($GLOBALS['TCA']['fe_groups'], $tca);

});
