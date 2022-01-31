<?php
	namespace Tilwa\IO\Http;

	use Tilwa\Contracts\Services\Decorators\OnlyLoadedBy;

	use Tilwa\Services\{ServiceCoordinator, InterceptsExternalPayload, Structures\OptionalDTO};

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Exception\Jobs\DeferExceptionAlert;

	use Psr\Http\Client\{ClientInterface, ClientExceptionInterface };

	use Psr\Http\Message\RequestFactoryInterface;

	class BaseHttpRequest extends InterceptsExternalPayload implements OnlyLoadedBy {

		protected $client, $requestFactory, $queueManager;

		public function __construct (ClientInterface $client, RequestFactoryInterface $requestFactory, AdapterManager $queueManager) {

			$this->client = $client;

			$this->requestFactory = $requestFactory;

			$this->queueManager = $queueManager;
		}

		final public function allowedConsumers ():array {

			return [ServiceCoordinator::class];
		}

		/**
		 * $request = $this->requestFactory->createRequest(GET, $url)
		 * return $this->client->sendRequest($request)
		*/
		abstract protected function makeRequest ();

		/**
		 * Work with [makeRequest]
		*/
		abstract protected function translate ():OptionalDTO;

		protected function translationFailure (Throwable $exception):OptionalDTO {

			if ($exception instanceof ClientExceptionInterface) // no response. we were unable to even send request

				$response = null;

			else $response = $this->makeRequest();

			$this->queueManager->augmentArguments(DeferExceptionAlert::class, [

				"explosive" => $exception,

				"activePayload" => $response
			]);

			return new OptionalDTO($response, false);
		}
	}
?>