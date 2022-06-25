<?php

$finder = (new PhpCsFixer\Finder())
    ->exclude(['bin', 'var', 'vendor', 'docs', 'docker'])
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'single_line_throw' => false,
        'array_syntax' => ['syntax' => 'short'],
        'class_definition' => false,
        'concat_space' => ['spacing' => 'one'],
        'native_function_invocation' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_types_order' => [
            'null_adjustment' => 'none',
            'sort_algorithm' => 'none',
        ],
        'no_superfluous_phpdoc_tags' => false,
        'braces' => false,
        'phpdoc_align' => false,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'continue',
                'declare',
                'do',
                'for',
                'foreach',
                'if',
                'include',
                'include_once',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
            ],
        ],
    ])
    ->setFinder($finder);
