<?php

	namespace Tilwa\Errors;

	class ExceptionRenderer {

		private $handlers;

		public function __construct(array $handlers) {

			$this->handlers = $handlers;
		}

		public function throw (int $errorCode):string {}
	}
?>