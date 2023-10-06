<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

defined('TYPO3') || die();

(static function ($extKey = 'easy_verein') {

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][$extKey] = \EHAERER\EasyVerein\Hooks\DataHandler::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey] = \EHAERER\EasyVerein\Hooks\DataHandler::class;

    /** add this to your site package to customize system email layout */
    // $GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'][785] = 'EXT:my_ext/Resources/Private/Layouts/Email/';
    /** add template root path for email layout */
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][785] = 'EXT:easy_verein/Resources/Private/Templates/Email/';

})();
