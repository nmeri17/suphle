<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Requests\RequestValidator;

	use Tilwa\Routing\PathPlaceholders;

	class ValidatorManager {

		private $placeholderStorage, $validator, $payloadStorage,

		$actionRules = [];

		public function __construct (RequestValidator $validator, PathPlaceholders $placeholderStorage, PayloadStorage $payloadStorage) {

			$this->validator = $validator;

			$this->placeholderStorage = $placeholderStorage;

			$this->payloadStorage = $payloadStorage;
		}

		public function validationErrors ():array {

			$valid = $this->validator

			->validate($this->mergedPayload(), $this->actionRules);

			return $valid->getErrors();
		}

		public function isValidated (): bool {

			return empty($this->validationErrors());
		}

		public function setValidationErrors(array $errors) {
			
			$this->validator->setErrors($errors);
		}

		private function mergedPayload ():array {

			return array_merge($this->placeholderStorage->getAllSegmentValues(), $this->payloadStorage->fullPayload());
		}

		public function setActionRules (array $rules):void {

			$this->actionRules = $rules;
		}
	}
?>