<?php

/*
 *  This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 *
 *  For the full copyright and license information, please read the
 *  LICENSE.md file that was distributed with this source code.
 */

defined('TYPO3_MODE') || die('Access denied.');

// Add special mfp palette
$GLOBALS['TCA']['sys_file_reference']['palettes']['mfpPalette'] = [
    'showitem' => 'title, alternative;;;;3-3-3,--linebreak--, description',
    'canNotCollapse' => true
];
