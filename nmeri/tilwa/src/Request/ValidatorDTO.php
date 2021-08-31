<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\RequestValidator;

	use Tilwa\Routing\PathPlaceholders;

	class ValidatorDTO {

		protected $validator;

		public $body;

		private $placeholderStorage;

		public function __construct (RequestValidator $validator, PathPlaceholders $placeholderStorage) {

			$this->validator = $validator;

			$this->placeholderStorage = $placeholderStorage;
		}

		public function validationErrors ():array {

			$valid = $this->validator

			->validate($this->placeholderStorage->getAllSegmentValues(), $this->rules()); // note: this currently only caters to GET requests

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