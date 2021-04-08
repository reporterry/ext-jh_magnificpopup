<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Configure frontend plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JhMagnificpopup',
    'Pi1',
    [
        \JonathanHeilmann\JhMagnificpopup\Controller\MagnificpopupController::class => 'show'
    ],
    []
);

// Save the IRRE content (use hook to change colPos)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['jh_magnificpopup'] = \JonathanHeilmann\JhMagnificpopup\Hooks\UpdateColPosHook::class;

// Register icon
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'tx-jhmagnificpopup-pi1',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:jh_magnificpopup/Resources/Public/Icons/ce_wiz.png']
);

// Page TSconfig to add new content element wizard and for global enabled iframe
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
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
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['jh_magnificpopup_ajax'] = \JonathanHeilmann\JhMagnificpopup\Core\EidRequest::class . '::run';
