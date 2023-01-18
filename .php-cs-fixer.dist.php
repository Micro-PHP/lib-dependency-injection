<?php

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'semicolon_after_instruction' => false,
        'header_comment' => [
            'header' => <<<EOF
 This file is part of the Micro framework package.
 
 (c) Stanislau Komar <kost@micro-php.net>
 
 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.
EOF
        ]
    ))
    ->setRiskyAllowed(true)
    ->setFinder($finder);