<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\RequestValidator;

	class BaseRequest {

		private $parameterList;

		private $validator;

		private $initiator;

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

		public function setPayload (array $payload):static {
			
			$this->parameterList = $payload;

			return $this;
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

		public function setInitiator ($user):static {
			
			$this->initiator = $user;

			return $this;
		}

		public function initiator () {
			
			return $this->initiator;
		}
	}
?>