<?php
	namespace Suphle\Auth\Middleware;

	use Suphle\Contracts\Auth\AuthStorage;

	use Suphle\Middleware\MiddlewareCollector;

	class AuthenticateCollector extends MiddlewareCollector {

		public function __construct (

			protected readonly array $activePatterns,

			public readonly AuthStorage $authStorage
		) {

			//
		}
	}
?>