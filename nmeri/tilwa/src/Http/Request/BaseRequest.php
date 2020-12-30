<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\RequestValidator;

	use Tilwa\Http\Request\Authenticator;

	class BaseRequest {

		private $parameterList;

		private $validator;

		private $userResolver;

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

			foreach ($newPairs as $key => $newValue)

				$this->parameterList[$key] = $newValue;
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

		public function setUserResolver (Authenticator $resolver):self {
			
			$this->userResolver = $resolver;

			return $this;
		}

		public function userResolver () { // access user by calling this method on your request object
			
			return $this->userResolver->getUser();
		}
	}
?>