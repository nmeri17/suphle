<?php
	 
	$finder = PhpCsFixer\Finder::create()
		->in(__DIR__);
	 
	$config = new PhpCsFixer\Config();
	$config
	->setRules([
		"@PSR12" => true
	])
	->setFinder($finder);
	 
	return $config;
?>