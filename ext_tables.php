<?php
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

if (!defined('TYPO3')) {
    die('Access denied.');
}

// get extension configuration
//$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['jh_magnificpopup']);
$colPosOfIrreContent = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('jh_magnificpopup', 'colPosOfIrreContent');

// Add frontend plugin
ExtensionUtility::registerPlugin(
    'jh_magnificpopup',
    'Pi1',
    'Magnific Popup'
);

// Add flexform for frontend plugin
$extensionName = GeneralUtility::underscoredToUpperCamelCase('jh_magnificpopup');
$pluginSignature = strtolower($extensionName) . '_pi1';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue($pluginSignature,
    'FILE:EXT:jh_magnificpopup/Configuration/FlexForms/MagnificpopupPlugin.xml');
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';

// Add static files
ExtensionManagementUtility::addStaticFile('jh_magnificpopup', 'Configuration/TypoScript/Default',
    'Magnific Popup');
ExtensionManagementUtility::addStaticFile('jh_magnificpopup', 'Configuration/TypoScript/Inline',
    'Magnific Popup - Content Element');

// Add colPos for content elements
if (!isset($GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][$colPosOfIrreContent])) {
	// Add the new colPos to the array, only if the ID does not exist...
	$GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][$colPosOfIrreContent] = ['jh_magnificpopup', $colPosOfIrreContent];
}

// Add special mfp palette
$GLOBALS['TCA']['sys_file_reference']['palettes']['mfpPalette'] = ['showitem' => 'title, alternative;;;;3-3-3,--linebreak--, description', 'canNotCollapse' => true];
