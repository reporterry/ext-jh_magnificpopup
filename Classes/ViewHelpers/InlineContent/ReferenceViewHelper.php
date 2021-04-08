<?php

/*
 *  This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 *
 *  For the full copyright and license information, please read the
 *  LICENSE.md file that was distributed with this source code.
 */

namespace JonathanHeilmann\JhMagnificpopup\ViewHelpers\InlineContent;

/*
 * This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 * This file is based on the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * ViewHelper used to render referenced content elements in Fluid templates
 */
class ReferenceViewHelper extends AbstractInlineContentViewHelper
{

    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument(
            'contentUids',
            'string',
            ''
        );
    }

    /**
     * Render method
     *
     * @return mixed
     */
    public function render()
    {
        if ('BE' === TYPO3_MODE) {
            return '';
        }

        $contentUids = explode(',', $this->arguments['contentUids']);

        // Get contentPids
        $contentPids = [];
        foreach ($contentUids as $uid) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $pid = $queryBuilder
                ->select('pid')
                ->from('tt_content')
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                ))->execute()->fetchOne();

            if(MathUtility::canBeInterpretedAsInteger($pid)) {
                $contentPids[] = $pid;
            }

        }
        $contentPids = array_unique($contentPids);

        // Get records
        $records = $this->getRecords([
            'uidInList' => $this->arguments['contentUids'],
            'pidInList' => '-1,' . implode(',', $contentPids),
            'includeRecordsWithoutDefaultTranslation' => !$this->arguments['hideUntranslated']
        ]);
        if (empty($records)) {
            return '';
        }

        // Reorder records by uid from contentUids
        $sortedRecords = [];
        foreach ($contentUids as $uid) {
            $sortedRecords[$uid] = $records[array_search($uid, array_column($records, 'uid'))];
        }

        // Render records
        $renderedRecords = $this->getRenderedRecords($sortedRecords);

        $content = implode(LF, $renderedRecords);
        return $content;
    }
}
