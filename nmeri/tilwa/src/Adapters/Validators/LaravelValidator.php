<?php
	namespace Tilwa\Adapters\Validators;

	use Tilwa\Contracts\Requests\RequestValidator;

	use Illuminate\Validation\Factory;

	class LaravelValidator implements RequestValidator {

		private $errorHolder, $client;

		public function __construct (Factory $client) {

			$this->client = $client;
		}

		public function validate (array $parameters, array $rules):void {

			$validator = $this->client->make($parameters, $rules);

			$this->errorHolder = $validator->errors();
		}

		public function getErrors ():iterable {

			return $this->errorHolder;
		}
	}
?>