<?php

defined('TYPO3_MODE') || die('Access denied.');


// get extension configuration
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['jh_magnificpopup']);

// Add flexform for frontend plugin
$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase('jh_magnificpopup');
$pluginSignature = strtolower($extensionName) . '_pi1';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature,
    'FILE:EXT:' . 'jh_magnificpopup' . '/Configuration/FlexForms/MagnificpopupPlugin.xml');
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';


// Add colPos for content elements
if (TYPO3_MODE == 'BE') {
    if (!isset($GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][$confArr['colPosOfIrreContent']])) {
        // Add the new colPos to the array, only if the ID does not exist...
        $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'][$confArr['colPosOfIrreContent']] = array(
            'jh_magnificpopup',
            $confArr['colPosOfIrreContent']
        );
    }
}

// Add frontend plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JonathanHeilmann.jh_magnificpopup',
    'Pi1',
    'Magnific Popup'
);