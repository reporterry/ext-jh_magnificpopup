<?php
namespace JonathanHeilmann\JhMagnificpopup\ViewHelpers\InlineContent;

use TYPO3\CMS\Core\Http\ApplicationType;
/*
 * This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 * This file is based on the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
/**
 * ViewHelper used to render inline content elements in Fluid templates
 */
class InlineViewHelper extends AbstractInlineContentViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument(
            'data',
            'array',
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
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            return '';
        }

        // Get records
        $records = $this->getRecords([
            'where' => 'tx_jhmagnificpopup_irre_parentid=' .
                ($this->arguments['data']['_LOCALIZED_UID'] ?? $this->arguments['data']['uid']
                ),
            'pidInList' => 'this',
            'includeRecordsWithoutDefaultTranslation' => !$this->arguments['hideUntranslated'],
            'orderBy' => 'sorting'
        ]);
        if (empty($records)) {
            return '';
        }

        // Render records
        $renderedRecords = $this->getRenderedRecords($records);
        return implode(LF, $renderedRecords);
    }
}
