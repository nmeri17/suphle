<?php

	namespace Tilwa\Http\Request;

	use Validator\RakitValidator;

	use Tilwa\Contracts\RequestValidator;

	class BaseRequest {

		private $parameterList;

		private $validator;

		public function __construct (RequestValidator $validator) {

			$this->validator = $validator;
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

			$valid = $this->validator

			->validate($this->parameterList, $this->rules());

			return $valid->getErrors();
		}

		public function isValidated (): bool {

			return empty($this->validationErrors());
		}

		protected function rules () {

			return [];
		}

		public function setValidationErrors(array $errors) {
			
			$this->validator->setErrors($errors);
		}
	}
?>