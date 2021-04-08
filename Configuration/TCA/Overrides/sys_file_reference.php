<?php

defined('TYPO3_MODE') || die('Access denied.');

// Add special mfp palette
$GLOBALS['TCA']['sys_file_reference']['palettes']['mfpPalette'] = array(
    'showitem' => 'title, alternative;;;;3-3-3,--linebreak--, description',
    'canNotCollapse' => true
);
