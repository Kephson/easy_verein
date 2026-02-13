<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3') || die();

call_user_func(static function ($extKey = 'easy_verein') {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',
        [
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
                    'default' => 0,
                ]
            ],
            'welcome_mail_sent' => [
                'exclude' => true,
                'label' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:fe_users.welcome_mail_sent',
                'config' => [
                    'type' => 'datetime',
                    'size' => 16,
                    'readOnly' => true,
                ]
            ],
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        'fe_users',
        'welcome-mail',
        'welcome_mail, welcome_mail_sent',
        ''
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        '--palette--;;welcome-mail',
        '',
        'after:lastlogin'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'fe_users',
        'easyverein_pk'
    );

    $GLOBALS['TCA']['fe_users']['ctrl']['searchFields'] = $GLOBALS['TCA']['fe_users']['ctrl']['searchFields'] . ',easyverein_pk';
    $GLOBALS['TCA']['fe_users']['ctrl']['label_alt'] = 'name';
    $GLOBALS['TCA']['fe_users']['ctrl']['label_alt_force'] = true;

});
