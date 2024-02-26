<?php
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use JonathanHeilmann\JhMagnificpopup\Hooks\UpdateColPosHook;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
if (!defined('TYPO3')) {
    die('Access denied.');
}

// Configure frontend plugin
ExtensionUtility::configurePlugin(
    'jh_magnificpopup',
    'Pi1',
    ['Magnificpopup' => 'show'],
    []
);

// Save the IRRE content (use hook to change colPos)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['jh_magnificpopup'] = UpdateColPosHook::class;

// Register icon
$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

// Page TSconfig to add new content element wizard and for global enabled iframe
ExtensionManagementUtility::addPageTSConfig('
    mod.wizards.newContentElement.wizardItems.plugins.elements.tx_jhmagnificpopup_pi1 {
        iconIdentifier = tx-jhmagnificpopup-pi1
        title = LLL:EXT:jh_magnificpopup/Resources/Private/Language/locallang.xml:pi1_title
        description = LLL:EXT:jh_magnificpopup/Resources/Private/Language/locallang.xml:pi1_plus_wiz_description
        tt_content_defValues {
            CType = list
            list_type = jhmagnificpopup_pi1
        }
    }
');

// Add eID for ajax-content
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['jh_magnificpopup_ajax'] = 'EXT:jh_magnificpopup/Resources/Public/Php/EidRunner.php';