<?php

	namespace Tilwa\Http\Request;

	// use Rakit\Validation\Validator;

	class BaseRequest {

		private $parameterList;

		public $validator;

		public function __get (string $parameterName) {

			return $this->parameterList[$parameterName];
		}

		public function payload () {

			return $this->parameterList;
		}

		public function updatePayload(array $newPairs) {

			foreach ($newPairs as $key => $newValue) {
				
				if (in_array($key, $this->parameterList))

					$this->parameterList[$key] = $newValue;
			}
		}

		public function replacePayload (array $payload) {
			
			$this->parameterList = $payload;
		}
	}