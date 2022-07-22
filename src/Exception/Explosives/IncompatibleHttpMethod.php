<?php
	namespace Suphle\Exception\Explosives;

	use Exception;

	class IncompatibleHttpMethod extends Exception {

		protected $code = 405;

		public function __construct (string $rendererMethod) {

			$this->message = "Expected HTTP method ". $rendererMethod;
		}
	}
?>