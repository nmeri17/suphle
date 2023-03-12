<?php
	namespace Suphle\Auth\Middleware;

	use Suphle\Middleware\MiddlewareCollector;

	class AuthorizeCollector extends MiddlewareCollector {

		public function __construct (

			protected readonly array $activePatterns,

			public readonly string $ruleClass
		) {

			//
		}
	}
?>