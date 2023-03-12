<?php
	namespace Suphle\Middleware;

	abstract class MiddlewareCollector {

		public function __construct (protected readonly array $activePatterns) {

			//
		}

		public function containsPattern (string $pattern):bool {

			return in_array($pattern, $this->activePatterns);
		}
	}
?>