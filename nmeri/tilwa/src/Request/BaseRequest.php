<?php

	namespace Tilwa\Request;

	use Tilwa\Contracts\RequestValidator;

	class BaseRequest {

		protected $validator;

		public $body;

		public function __construct (RequestValidator $validator) {

			$this->validator = $validator;
		}

		public function getPlaceholders () {

			return array_filter(get_object_vars($this), function ($property) {

				return $property != "validator";
			}, ARRAY_FILTER_USE_KEY);
		}

		public function updatePlaceholders(array $newPairs):self {

			foreach ($newPairs as $key => $newValue)

				if (property_exists($this, $key))

					$this->$key = $newValue;

			return $this;
		}

		public function setPlaceholders (array $payload):self {
			
			return $this->updatePlaceholders($payload);
		}

		public function validationErrors ():array {

			$valid = $this->validator

			->validate($this->getPlaceholders(), $this->rules());

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