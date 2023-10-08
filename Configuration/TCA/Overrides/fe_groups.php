<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3') || die();

call_user_func(static function ($extKey = 'easy_verein') {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups',
        [
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
        ]
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_groups',
        'easyverein_g_short',
        '',
        'after:subgroup'
    );

    $GLOBALS['TCA']['fe_groups']['ctrl']['searchFields'] = $GLOBALS['TCA']['fe_groups']['ctrl']['searchFields'] . ',easyverein_g_short';

});
