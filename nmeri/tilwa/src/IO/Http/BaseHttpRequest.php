<?php
	namespace Tilwa\IO\Http;

	use Tilwa\Contracts\{Services\Decorators\OnlyLoadedBy, IO\HttpClient};

	use Tilwa\Controllers\{ServiceCoordinator, InterceptsExternalPayload};

	class BaseHttpRequest extends InterceptsExternalPayload implements OnlyLoadedBy {

		protected $client;

		public function __construct (HttpClient $client) {

			$this->client = $client;
		}

		final public function allowedConsumers ():array {

			return [ServiceCoordinator::class];
		}
	}
?>