<?php

/*
 *  This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 *
 *  For the full copyright and license information, please read the
 *  LICENSE.md file that was distributed with this source code.
 */

defined('TYPO3_MODE') || die('Access denied.');

// Add static files
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'jh_magnificpopup',
    'Configuration/TypoScript/Default',
    'Magnific Popup'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'jh_magnificpopup',
    'Configuration/TypoScript/Inline',
    'Magnific Popup - Content Element'
);
