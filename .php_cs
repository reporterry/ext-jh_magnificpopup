<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()
       ->in(__DIR__ . '/Classes')
       ->in(__DIR__ . '/Configuration')
;

$header = <<<EOF
 This file is part of the JonathanHeilmann\JhMagnificpopup extension under GPLv2 or later.
 
 For the full copyright and license information, please read the
 LICENSE.md file that was distributed with this source code.
EOF;

$config->addRules([
    'header_comment' => ['header' => $header, 'separate' => 'both'],
]);

return $config;
