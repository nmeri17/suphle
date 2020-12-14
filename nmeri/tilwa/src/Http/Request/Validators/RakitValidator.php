<?php

	namespace Tilwa\Http\Request\Validators;

	use Rakit\Validation\{Validator, ErrorBag};

	use Tilwa\Contracts\RequestValidator;

	class RakitValidator implements RequestValidator {

		private $validator;

		private $errorHolder;

		public function validate (array $parameters, array $rules):void {

			$this->validator = (new Validator)

			->validate($parameters, $rules);

			$this->errorHolder = $this->validator->errors();
		}

		public function getErrors ():array {

			return $this->errorHolder->all();
		}

		public function setErrors (array $errors):void {
			
			$this->errorHolder = new ErrorBag($errors);
		}
	}
?>