<?php

	namespace Tilwa\Errors;

	class IncompatibleService extends Exception {

		public function __construct($service, $code = 0, Throwable $previous = null) {

		    $message = "The offending service is: $service";

		    parent::__construct($message, $code, $previous);
		}
	}
?>