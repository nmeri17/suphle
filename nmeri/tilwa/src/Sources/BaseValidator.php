<?php

	namespace Tilwa\Sources;

	use Controllers\Bootstrap;

	use Rakit\Validation\Validator;

	/* intended for post requests but will be called before the request handler. on validation failure in any of this class methods, returned data will be passed to the previous route*/
	class BaseValidator {

		protected $validator;

		protected $app;

		function __construct (Bootstrap $app, Validator $validator ) {

			$this->app = $app;

			$this->validator = $validator;
		}
	}

?>