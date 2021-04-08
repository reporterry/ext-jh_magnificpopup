<?php

/*
 *  This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 *
 *  For the full copyright and license information, please read the
 *  LICENSE.md file that was distributed with this source code.
 */

namespace JonathanHeilmann\JhMagnificpopup\ViewHelpers\Format;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class UnderscoredToUpperCamelCaseViewHelper extends AbstractViewHelper
{

    /**
     * @return string
     */
    public function render()
    {
        $string = $this->arguments['string'];
        if ($string === null) {
            $string = $this->renderChildren();
        }
        return GeneralUtility::underscoredToUpperCamelCase($string);
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('string', 'string', '', false);
    }
}
