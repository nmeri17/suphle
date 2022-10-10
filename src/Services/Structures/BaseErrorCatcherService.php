<?php
	namespace Suphle\Services\Structures;

	use Suphle\Contracts\Services\Decorators\VariableDependencies;

	use Suphle\Routing\PathPlaceholders;

	use Suphle\Request\PayloadStorage;

	/**
	 * @requires VariableDependencies
	*/
	trait BaseErrorCatcherService {

		protected $erroneousMethod, $payloadStorage, $pathPlaceholders;

		public function rethrowAs ():array {

			return [];
		}

		public function failureState (string $method) {

			//
		}

		public function lastErrorMethod ():?string {

			return $this->erroneousMethod;
		}

		public function didHaveErrors (string $method):void {

			$this->erroneousMethod = $method;
		}

		public function matchesErrorMethod (string $method):bool {

			return $method == $this->erroneousMethod;
		}

		public function getDebugDetails () {

			return $this->payloadStorage->fullPayload();
		}

		public function dependencyMethods ():array {

			return [

				"setPayloadStorage", "setPlaceholderStorage"
			];
		}

		public function setPayloadStorage (PayloadStorage $payloadStorage):void {

			$this->payloadStorage = $payloadStorage;
		}

		public function setPlaceholderStorage (PathPlaceholders $pathPlaceholders):void {

			$this->pathPlaceholders = $pathPlaceholders;
		}
	}
?>