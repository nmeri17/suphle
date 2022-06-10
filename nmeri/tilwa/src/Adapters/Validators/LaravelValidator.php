<?php
	namespace Tilwa\Adapters\Validators;

	use Tilwa\Contracts\{Requests\RequestValidator, Bridge\LaravelContainer};

	use Illuminate\Validation\Factory;

	class LaravelValidator implements RequestValidator {

		private $errorHolder, $client, $laravelContainer;

		public function __construct (LaravelContainer $laravelContainer) {

			$this->laravelContainer = $laravelContainer;
		}

		public function validate (array $parameters, array $rules):void {

			if (is_null($this->client))

				$this->client = $this->laravelContainer->make(Factory::class);

			$validator = $this->client->make($parameters, $rules);

			$this->errorHolder = $validator->errors();
		}

		public function getErrors ():iterable {

			return $this->errorHolder->all();
		}
	}
?>