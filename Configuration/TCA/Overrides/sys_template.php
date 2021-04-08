<?php

defined('TYPO3_MODE') || die('Access denied.');

// Add static files
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('jh_magnificpopup', 'Configuration/TypoScript/Default',
    'Magnific Popup');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('jh_magnificpopup', 'Configuration/TypoScript/Inline',
    'Magnific Popup - Content Element');
