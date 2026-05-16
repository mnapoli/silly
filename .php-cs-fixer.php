<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src');

return (new PhpCsFixer\Config)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@PHP7x0Migration' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'braces_position' => [
            'allow_single_line_anonymous_functions' => true,
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'new_with_parentheses' => false,
        'multiline_whitespace_before_semicolons' => false,
        'no_php4_constructor' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'phpdoc_add_missing_param_annotation' => false,
        'phpdoc_align' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_separation' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_var_without_name' => true,
        'pow_to_exponentiation' => true,
        'unary_operator_spaces' => false,
        'semicolon_after_instruction' => true,
        'strict_param' => true,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
