<?php
	namespace Suphle\Adapters\Validators;

	use Suphle\Contracts\Requests\RequestValidator;

	use Illuminate\Validation\Factory;

	class LaravelValidator implements RequestValidator {

		private $errorHolder;

		public function __construct(private readonly Factory $client)
  {
  }

		public function validate (array $parameters, array $rules):void {

			$validator = $this->client->make($parameters, $rules);

			$this->errorHolder = $validator->errors();
		}

		public function getErrors ():iterable {

			return $this->errorHolder->messages();
		}
	}
?>