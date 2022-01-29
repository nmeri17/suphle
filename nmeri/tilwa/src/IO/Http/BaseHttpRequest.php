<?php
	namespace Tilwa\IO\Http;

	use Tilwa\Contracts\Services\Decorators\OnlyLoadedBy;

	use Tilwa\Services\{ServiceCoordinator, InterceptsExternalPayload};

	use Psr\Http\Client\{ClientInterface, ClientExceptionInterface };

	use Psr\Http\Message\RequestFactoryInterface;

	/**
	 * Sample usage in [translate]:
	 * $request = $this->requestFactory->createRequest(GET, $url)
	 * return $this->client->sendRequest($request)
	*/
	class BaseHttpRequest extends InterceptsExternalPayload implements OnlyLoadedBy {

		protected $client, $requestFactory;

		public function __construct (ClientInterface $client, RequestFactoryInterface $requestFactory) {

			$this->client = $client;

			$this->requestFactory = $requestFactory;
		}

		final public function allowedConsumers ():array {

			return [ServiceCoordinator::class];
		}
	}
?>