<?php

$finder = PhpCsFixer\Finder::create()
        ->in(__DIR__ . '/src')
        ->in(__DIR__ . '/test')
        ->name('*.php');

return (new PhpCsFixer\Config())
                ->setRiskyAllowed(false)
                ->setRules([
                    '@PSR12'                 => true,
                    'array_syntax'           => ['syntax' => 'short'],
                    // Preserve the project's same-line brace style for classes/functions.
                    'braces_position'        => [
                        'classes_opening_brace'   => 'same_line',
                        'functions_opening_brace' => 'same_line',
                    ],
                    // Keep the author's aligned assignments / arrows.
                    'binary_operator_spaces' => [
                        'default'   => 'single_space',
                        'operators' => [
                            '='  => 'align_single_space_minimal',
                            '=>' => 'align_single_space_minimal',
                        ],
                    ],
                    'no_unused_imports'      => true,
                    'ordered_imports'        => ['sort_algorithm' => 'alpha'],
                    'single_quote'           => true,
                ])
                ->setFinder($finder);
