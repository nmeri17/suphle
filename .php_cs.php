<?php
	 
	$finder = PhpCsFixer\Finder::create()
		->in(__DIR__);
	 
	$config = new PhpCsFixer\Config();
	$config
	->setRules([
		"array_syntax" => ["syntax" => "short"],
		"binary_operator_spaces" => [
			"operators" => [
				"=" => "align_single_space_minimal",
				"=>" => "align_single_space_minimal",
			],
		],
		"concat_space" => ["spacing" => "none"],
		"ordered_imports" => ["sort_algorithm" => "alpha"],
		"no_extra_blank_lines" => true,
		"no_whitespace_in_blank_line" => true,
		"phpdoc_align" => [
			"align" => "left",
		],
		"phpdoc_no_access" => true,
		"phpdoc_no_package" => true,
		"phpdoc_scalar" => true,
		"phpdoc_single_line_var_spacing" => true,
		"phpdoc_summary" => true,
		"phpdoc_to_comment" => true,
		"phpdoc_trim" => true,
		"phpdoc_types" => true,
		"phpdoc_var_without_name" => true,
		"return_type_declaration" => true,
		"single_quote" => false,
		"sort_array_syntax" => ["syntax" => "alphabetical"],
		"standardize_not_equals" => true,
		"ternary_operator_spaces" => true,
		"trailing_comma_in_multiline" => true,
		"trim_array_spaces" => true,
		"no_unused_imports" => true,
		"declare_strict_types" => true,
		"single_line_comment_style" => ["comment_types" => ["hash"]],
		"blank_line_before_statement" => true, // <--- New rule
		"line_ending" => "\n", // <--- New rule
		"linebreak_after_opening_tag" => true,
		"no_leading_namespace_whitespace" => true,
		"single_blank_line_before_namespace" => false,
		"no_trailing_comma_in_singleline_array" => true,
		"no_whitespace_before_comma_in_array" => true,
		"lowercase_keywords" => true,
		"php_unit_test_class_requires_covers" => false,
		"php_unit_strict" => true,
		"php_unit_test_case_static_method_calls" => ["call_type" => "this"],
		"brace_style" => ["allow_single_line_closure" => true, "position_after_functions_and_oop_constructs" => "same_line"], // <--- Updated rule
		"line_length" => ["max" => 75], // <--- New rule
		"string_notation" => true, // <--- New rule
		"group_import" => true, // <--- New rule
	])
	->setFinder($finder);
	 
	return $config;
?>