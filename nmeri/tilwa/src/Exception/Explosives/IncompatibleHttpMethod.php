<?php
	namespace Tilwa\Exception\Explosives;

	use Exception;

	class IncompatibleHttpMethod extends Exception {

		private $rendererMethod;

		public function __construct (string $rendererMethod) {

			$this->rendererMethod = $rendererMethod;
		}

		public function getCode ():int {

			return 405;
		}

		public function getMessage ():string {

			return "Expected HTTP method ". $this->rendererMethod;
		}
	}