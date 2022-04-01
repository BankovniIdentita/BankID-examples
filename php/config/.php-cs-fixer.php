<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/../src');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'no_superfluous_phpdoc_tags' => false,
        'no_unused_imports' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'yoda_style' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => false,
        'phpdoc_align' => false,
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
            ],
        ],
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'parameters',
            ],
        ],
        'array_indentation' => true,
        'braces' => true,
        'method_argument_space' => true,
    ])
    ->setFinder($finder);
