<?php

	namespace Tilwa\Http\Request;

	use Rakit\Validation\Validator;

	class BaseRequest {

		private $parameterList;

		private $validator;

		public function __construct () {

			$this->validator = new Validator;
		}

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

		public function validationErrors ():array {

			return $this->validator->validate()->errors()->all();
		}

		public function validated (): bool {

			return empty($this->validationErrors());
		}
	}
?>