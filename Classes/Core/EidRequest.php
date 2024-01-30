<?php
namespace JonathanHeilmann\JhMagnificpopup\Core;

/*
 * This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;

/**
 * Class tu handle the eID request.
 */
class EidRequest
{

    /**
     * @var TypoScriptFrontendController $typoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     *
     *
     * @return string
     */
    public function run()
    {
        $cObjConfig = [];
        $cObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $gp = GeneralUtility::_GP('jh_magnificpopup');
        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($GLOBALS['TSFE']->tmpl->setup['tt_content.']);
        //http://lists.typo3.org/pipermail/typo3-german/2011-July/079128.html
        switch ($gp['type']) {
            case 'inline':
                $cObjConfig = ['name'    =>    'CONTENT', 'conf'    => ['table'    =>    'tt_content', 'select.'    => ['where'        => 'tx_jhmagnificpopup_irre_parentid='.$gp['irre_parrentid'], 'pidInList'    => (GeneralUtility::_GP('id')?:$GLOBALS["TSFE"]->id), 'languageField'    => 'sys_language_uid', 'orderBy'    => 'sorting'], 'wrap'    => '<div class="white-popup-block">|</div>', 'renderObj'    => $GLOBALS['TSFE']->tmpl->setup['tt_content'], 'renderObj.'    => $GLOBALS['TSFE']->tmpl->setup['tt_content.']]];
                break;
            case 'reference':
                $pid = (isset($gp['pid']) && !empty($gp['pid']) ? $gp['pid'] : $GLOBALS["TSFE"]->id);
                $cObjConfig = ['name'    =>    'CONTENT', 'conf'    => ['table'    =>    'tt_content', 'select.'    => ['uidInList'        => $gp['uid'], 'pidInList'    => $pid, 'orderBy'    => 'sorting', 'languageField'    => 'sys_language_uid'], 'wrap'    => '<div class="white-popup-block">|</div>', 'renderObj'    => $GLOBALS['TSFE']->tmpl->setup['tt_content'], 'renderObj.'    => $GLOBALS['TSFE']->tmpl->setup['tt_content.']]];
                break;
            default:
                if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jh_magnificpopup']['EidTypeHook']) {
                    if (!isset($gp['hookConf'])) {
                        $gp['hookConf'] = '';
                    }
                    $params = ['type' => $gp['type'], 'hookConf' => $gp['hookConf']];
                    foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jh_magnificpopup']['EidTypeHook'] as $_funcRef) {
                        if ($_funcRef) {
                            $cObjConfig = GeneralUtility::callUserFunction($_funcRef, $params, $this);
                            if (isset($cObjConfig['matchedType']) && $cObjConfig['matchedType'] === true) {
                                break;
                            }
                        }
                    }
                    if (isset($cObjConfig['matchedType']) && $cObjConfig['matchedType'] === false) {
                        $cObjConfig = null;
                    }
                }
        }
        if (!empty($cObjConfig) && is_array($cObjConfig)) {
            $this->typoScriptFrontendController->content = $cObject->getContentObject($cObjConfig['name'])->render($cObjConfig['conf']);
        } else {
            $this->typoScriptFrontendController->content = 'ERROR - no (or wrong) configuration';
        }

        if ($GLOBALS['TSFE']->isINTincScript()) {
            $GLOBALS['TSFE']->INTincScript();
        }

        if (isset($cObjConfig['wrap']) && !empty($cObjConfig['wrap'])) {
            $this->typoScriptFrontendController->content = $cObject->wrap($this->typoScriptFrontendController->content, $cObjConfig['wrap']);
        }

        return $this->typoScriptFrontendController->content;
    }

    /**
     * EidRequest constructor.
     * Initialize frontend environment
     */
    public function __construct()
    {
        $this->bootstrap = GeneralUtility::makeInstance(Bootstrap::class);

        $feUserObj = EidUtility::initFeUser();
        EidUtility::initTCA();

        $pageId = GeneralUtility::_GET('id') ?: 1;
        $pageType = GeneralUtility::_GET('type') ?: 0;

        $this->typoScriptFrontendController = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            $pageId,
            $pageType,
            true
        );
        $GLOBALS['TSFE'] = $this->typoScriptFrontendController;
        $this->typoScriptFrontendController->connectToDB();
        $this->typoScriptFrontendController->fe_user = $feUserObj;
        $this->typoScriptFrontendController->id = $pageId;
        $this->typoScriptFrontendController->checkAlternativeIdMethods();
        $this->typoScriptFrontendController->determineId();
        $this->typoScriptFrontendController->initTemplate();
        $this->typoScriptFrontendController->getConfigArray();
        $this->typoScriptFrontendController->settingLanguage();
        $this->typoScriptFrontendController->settingLocale();
    }
}
