<?php

/*
 *  This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 *
 *  For the full copyright and license information, please read the
 *  LICENSE.md file that was distributed with this source code.
 */

namespace JonathanHeilmann\JhMagnificpopup\Controller;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Class MagnificpopupController
 */
class MagnificpopupController extends ActionController
{

    /**
     * Array of supported content template packages/extensions
     *
     * @var array
     */
    protected static $supportedContentTemplateExtensions = [
        'bootstrap_package',
        'fluid_styled_content',
        'css_styled_content'
    ];

    /**
     * SignalSlotDispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * PageRepository
     *
     * @var \TYPO3\CMS\Core\Domain\Repository\PageRepository
     */
    protected $pageRepository;

    /**
     * ContentObject
     *
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObj;

    /**
     * Data
     *
     * @var array
     */
    protected $data;

    private static function unQuoteFilenames($parameters, $unQuote = false)
    {
        $paramsArr = explode(' ', trim($parameters));
        // Whenever a quote character (") is found, $quoteActive is set to the element number inside of $params. A value of -1 means that there are not open quotes at the current position.
        $quoteActive = -1;
        foreach ($paramsArr as $k => $v) {
            if ($quoteActive > -1) {
                $paramsArr[$quoteActive] .= ' ' . $v;
                unset($paramsArr[$k]);
                if (substr($v, -1) === $paramsArr[$quoteActive][0]) {
                    $quoteActive = -1;
                }
            } elseif (!trim($v)) {
                // Remove empty elements
                unset($paramsArr[$k]);
            } elseif (preg_match('/^(["\'])/', $v) && substr($v, -1) !== $v[0]) {
                $quoteActive = $k;
            }
        }
        if ($unQuote) {
            foreach ($paramsArr as $key => &$val) {
                $val = preg_replace('/(?:^"|"$)/', '', $val);
                $val = preg_replace('/(?:^\'|\'$)/', '', $val);
            }
            unset($val);
        }
        // Return reindexed array
        return array_values($paramsArr);
    }

    /**
     * action show
     *
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function showAction()
    {
        // Assign multiple values
        $viewAssign = [];
        $this->cObj = $this->configurationManager->getContentObject();
        $this->data = $this->cObj->data;

        // Get localized record
        $localizedRecord = $this->pageRepository->getRecordOverlay(
            'tt_content',
            $this->data,
            GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id'),
            GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'legacyLanguageMode')
        );
        if ($localizedRecord !== false && isset($localizedRecord['_LOCALIZED_UID'])) {
            $this->data = $localizedRecord;
        }

        $viewAssign['uid'] = $this->data['uid'];
        $viewAssign['data'] = $this->data;

        $viewAssign['contentTemplateExtension'] = 'custom';
        foreach (self::$supportedContentTemplateExtensions as $contentTemplateExtension) {
            if (ExtensionManagementUtility::isLoaded($contentTemplateExtension)) {
                $viewAssign['contentTemplateExtension'] = $contentTemplateExtension;
                break;
            }
        }
        switch ($this->settings['contenttype']) {
            case 'iframe':
                ArrayUtility::mergeRecursiveWithOverrule($viewAssign, $this->iframe());
                break;
            case 'reference':
                if (
                    $this->settings['content']['procedure_reference'] == 'ajax') {
                    ArrayUtility::mergeRecursiveWithOverrule($viewAssign, $this->ajax());
                } elseif (
                    $this->settings['content']['procedure_reference'] == 'inline') {
                    ArrayUtility::mergeRecursiveWithOverrule($viewAssign, $this->inline());
                } elseif (!$this->settings['content']['procedure_reference']) {
                    // Add error if no method (inline or ajax) has been selected
                    $this->addFlashMessage(
                        'Please select the method (inline or ajax) to display Magnific Popup content',
                        'Select method',
                        AbstractMessage::WARNING
                    );
                } elseif (!$this->settings['content']['reference']) {
                    // Add error if no content has been selected
                    $this->addFlashMessage(
                        'Please select a content to display with Magnific Popup',
                        'Select content',
                        AbstractMessage::WARNING
                    );
                } else {
                    // Add general error
                    $this->addFlashMessage(
                        'Please check your Magnific Popup Plugin configuration',
                        'Failure in plugin config',
                        AbstractMessage::WARNING
                    );
                }
                break;
            case 'inline':
                if ($this->settings['content']['procedure_inline'] == 'ajax') {
                    ArrayUtility::mergeRecursiveWithOverrule($viewAssign, $this->ajax());
                } elseif ($this->settings['content']['procedure_inline'] == 'inline') {
                    ArrayUtility::mergeRecursiveWithOverrule($viewAssign, $this->inline());
                } elseif (!$this->settings['content']['procedure_inline']) {
                    // Add error if no method (inline or ajax) has been selected
                    $this->addFlashMessage(
                        'Please select the method (inline or ajax) to display Magnific Popup content',
                        'Select method',
                        AbstractMessage::WARNING
                    );
                } elseif (!$this->settings['content']['inline']) {
                    // Add error if no content has been selected
                    $this->addFlashMessage(
                        'Please select a content to display with Magnific Popup',
                        'Select content',
                        AbstractMessage::WARNING
                    );
                } else {
                    // Add general error
                    $this->addFlashMessage(
                        'Please check your Magnific Popup Plugin configuration',
                        'Failure in plugin config',
                        AbstractMessage::WARNING
                    );
                }
                break;
            default:
                // Add error if no "Display type" has been selected
                $this->addFlashMessage(
                    'Please select a "Display type" to use Magnific Popup',
                    'Select "Display type"',
                    AbstractMessage::WARNING
                );
        }

        // Signal for show action (may be used to modify the array assigned to fluid-template)
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [
                'data' => $this->data,
                'settings' => $this->settings,
                'viewAssign' => &$viewAssign
            ]
        );

        // Assign array to fluid-template
        $this->view->assignMultiple($viewAssign);
    }

    /**
     * @return array
     */
    protected function ajax()
    {
        $viewAssign['type'] = 'ajax';
        // Use ajax procedure
        if ($this->settings['contenttype'] == 'reference') {
            // Get the list of pid's
            $uidArray = explode(',', $this->settings['content']['reference']);
            $pidInList = [];
            foreach ($uidArray as $uid) {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
                $pid = $queryBuilder
                    ->select('pid')
                    ->from('tt_content')
                    ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                    ))->execute()->fetchOne();

                if(MathUtility::canBeInterpretedAsInteger($pid)) {
                    $pidInList[] = $pid;
                }
            }
            // Configure the link
            $linkconf['parameter'] = $this->data['pid'];
            $linkconf['additionalParams'] = '&' .
                ($this->settings['useEidForAjaxMethod'] != 1 ? 'type=109' : 'eID=jh_magnificpopup_ajax') .
                '&jh_magnificpopup[type]=reference&jh_magnificpopup[uid]=' . $this->settings['content']['reference'] .
                '&jh_magnificpopup[pid]=' . implode(',', $pidInList);
        } else {
            // Configure the link
            $linkconf['parameter'] = $this->data['pid'];
            $linkconf['additionalParams'] = '&' .
                ($this->settings['useEidForAjaxMethod'] != 1 ? 'type=109' : 'eID=jh_magnificpopup_ajax') .
                '&jh_magnificpopup[type]=inline&jh_magnificpopup[irre_parrentid]=' . $this->data['uid'];
        }
        // Link-setup
        $lConf = [];
        $lConf['ATagParams'] = 'class="mfp-ajax-' . $this->data['uid'] . '"';
        $lConf['parameter'] = $linkconf['parameter'];
        $lConf['additionalParams'] = $linkconf['additionalParams'];

        if ($this->settings['linktype'] == 'file') {
            ArrayUtility::mergeRecursiveWithOverrule($viewAssign, $this->renderLinktypeFile($lConf));
        } else {
            $viewAssign['tsLink'] = $this->cObj->typoLink($this->settings['mfpOption']['text'], $lConf);
        }

        // Get settings from flexform
        $this->getSettingsFromFlexform('ajax');

        $viewAssign['settings'] = $this->settings;

        return $viewAssign;
    }

    /**
     * @return array
     */
    protected function inline()
    {
        $viewAssign['type'] = 'inline';

        // Link-setup
        $lConf['ATagParams'] = 'class="mfp-inline-' . $this->data['uid'] . '" data-mfp-src="#mfp-inline-' . $this->data['uid'] . '"';
        $lConf['parameter'] = $GLOBALS['TSFE']->id;

        if ($this->settings['linktype'] == 'file') {
            ArrayUtility::mergeRecursiveWithOverrule($viewAssign, $this->renderLinktypeFile($lConf));
        } else {
            $viewAssign['tsLink'] = $this->cObj->typoLink($this->settings['mfpOption']['text'], $lConf);
        }

        // Get settings from flexform
        $this->getSettingsFromFlexform('inline');

        $viewAssign['settings'] = $this->settings;

        return $viewAssign;
    }

    /**
     * @return array
     */
    protected function iframe()
    {
        $viewAssign['type'] = 'iframe';

        // Link-setup
        $lConf = $this->configureLink($selectorClass = 'mfp-iframe-' . $this->data['uid']);

        if ($this->settings['linktype'] == 'file') {
            ArrayUtility::mergeRecursiveWithOverrule($viewAssign, $this->renderLinktypeFile($lConf));
        } else {
            $viewAssign['tsLink'] = $this->cObj->typoLink($this->settings['mfpOption']['text'], $lConf);
        }

        // Get settings from flexform
        $this->getSettingsFromFlexform('iframe');

        $viewAssign['settings'] = $this->settings;
        return $viewAssign;
    }

    /**
     * Configure the link
     *
     * @param string $selectorClass
     * @return array
     */
    protected function configureLink($selectorClass)
    {
        $lConf = [];
        // Modify parameter to add jQuery selector class to link
        $parameter = $this->settings['mfpOption']['href'];
        $parameters = self::unQuoteFilenames($parameter, true);
        if (count($parameters) >= 3) {
            $parameters[2] = $parameters[2] . ' ' . $selectorClass;
            // Quote values (has been unquoted by GeneralUtility::unQuoteFilenames)
            foreach ($parameters as $key => $value) {
                $parameters[$key] = '"' . $value . '"';
            }
            $parameter = implode(' ', $parameters);
        } else {
            $lConf['ATagParams'] = 'class="' . $selectorClass . '"';
        }

        $lConf['parameter'] = $parameter;

        return $lConf;
    }

    /**
     * Render link of type file
     *
     * @param array $lConf
     * @return array
     */
    protected function renderLinktypeFile($lConf)
    {
        $viewAssign = [];

        // Get file
        /** @var FileRepository $fileRepository */
        $fileRepository = $this->objectManager->get(FileRepository::class);
        $fileObjects = $fileRepository->findByRelation(
            'tt_content',
            'mfp_image',
            isset($this->data['_LOCALIZED_UID']) ? $this->data['_LOCALIZED_UID'] : $this->data['uid']
        );

        if (!empty($fileObjects)) {
            /** @var \TYPO3\CMS\Core\Resource\FileReference $file */
            $file = $fileObjects[0];
            $this->cObj->setCurrentFile($file->getOriginalFile());
            $imageConf = $GLOBALS['TSFE']->tmpl->setup['lib.']['tx_jhmagnificpopup_pi1.']['image.'];
            $imageConf['file.']['treatIdAsReference'] = 1;
            $imageConf['file'] = $file;
            $imageConf['altText'] = $file->getProperty('alternative');
            $imageConf['titleText'] = $file->getProperty('title');
            if (isset($this->settings['mfpOption']['file_width']) && !empty($this->settings['mfpOption']['file_width'])) {
                $imageConf['file.']['maxW'] = $this->settings['mfpOption']['file_width'];
            }

            if (isset($this->settings['mfpOption']['file_height']) && !empty($this->settings['mfpOption']['file_height'])) {
                $imageConf['file.']['maxH'] = $this->settings['mfpOption']['file_height'];
            }

            // Render image
            $theImgCode = $this->cObj->cObjGetSingle('IMAGE', $imageConf);

            // Get image orientation
            switch ($this->settings['mfpOption']['file_orient']) {
                case 1:
                    $viewAssign['imageorient'] = 'right';
                    break;
                case 2:
                    $viewAssign['imageorient'] = 'left';
                    break;
                case 0:
                default:
                    $viewAssign['imageorient'] = 'center';
            }
            // Get image description/caption
            $viewAssign['imagecaption'] = $file->getProperty('description');
            $viewAssign['linkedImagecaption'] = $this->cObj->typoLink($viewAssign['imagecaption'], $lConf);

            // Render typolink
            $viewAssign['tsLink'] = $this->cObj->typoLink($theImgCode, $lConf);
        } else {
            $this->addFlashMessage('Please select an image', 'No image', AbstractMessage::WARNING);
        }

        return $viewAssign;
    }

    /**
     * Get settings from flexform
     * If something else than the default from setup is selected or a value is empty use setting from flexform
     *
     * @param string $forType
     */
    protected function getSettingsFromFlexform($forType)
    {
        foreach ($this->settings['mfpOption'] as $key => $value) {
            if ($value != -1 && !empty($value)) {
                $this->settings['type'][$forType][$key] = $value == 'local' ? $this->settings['mfpOption'][$key . '_local'] : $value;
            }
        }
    }

    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher): void
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    public function injectPageRepository(PageRepository $pageRepository): void
    {
        $this->pageRepository = $pageRepository;
    }
}
