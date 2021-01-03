<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\RequestValidator;

	class BaseRequest {

		private $validator;

		public function __construct (RequestValidator $validator) {

			$this->validator = $validator;
		}

		public function payload () {

			return $this->parameterList;
		}

		public function updatePayload(array $newPairs):self {

			foreach ($newPairs as $key => $newValue)

				if (property_exists($this, $key))

					$this->$key = $newValue;

			return $this;
		}

		public function setPayload (array $payload):self {
			
			return $this->updatePayload($payload);
		}

		public function validationErrors ():array {

			$valid = $this->validator

			->validate($this->parameterList, $this->rules()); // continue here

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