<?php
namespace JonathanHeilmann\JhMagnificpopup\ViewHelpers\PageRenderer;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Page\PageRenderer;

/*
 * This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Class AddJsInlineCodeViewHelper
 * @package JonathanHeilmann\JhMagnificpopup\ViewHelpers\PageRenderer
 */
class AddJsInlineCodeViewHelper extends AbstractViewHelper
{

    public function render()
    {
        $name = $this->arguments['name'];
        $block = $this->arguments['block'];
        $compress = $this->arguments['compress'];
        $forceOnTop = $this->arguments['forceOnTop'];
        $addToFooter = $this->arguments['addToFooter'];
        if ($block === null) $block = $this->renderChildren();
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = $this->objectManager->get(PageRenderer::class);
        if ($addToFooter === false)
        {
            $pageRenderer->addJsInlineCode($name, $block, $compress, $forceOnTop);
        } else{
            $pageRenderer->addJsFooterInlineCode($name, $block, $compress, $forceOnTop);
        }
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', '', true);
        $this->registerArgument('block', 'mixed', '', false);
        $this->registerArgument('compress', 'bool', '', false);
        $this->registerArgument('forceOnTop', 'bool', '', false);
        $this->registerArgument('addToFooter', 'bool', '', false);
    }

}