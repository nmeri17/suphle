<?php
	namespace Tilwa\IO\Http;

	use Tilwa\Contracts\Services\Decorators\OnlyLoadedBy;

	use Tilwa\Services\{ServiceCoordinator, InterceptsExternalPayload, Structures\OptionalDTO};

	use Tilwa\Exception\DetectedExceptionManager;

	use Psr\Http\Client\{ClientInterface, ClientExceptionInterface };

	use Psr\Http\Message\RequestFactoryInterface;

	class BaseHttpRequest extends InterceptsExternalPayload implements OnlyLoadedBy {

		protected $client, $requestFactory, $exceptionManager,

		$requestResponse;

		public function __construct (ClientInterface $client, RequestFactoryInterface $requestFactory, DetectedExceptionManager $exceptionManager) {

			$this->client = $client;

			$this->requestFactory = $requestFactory;

			$this->exceptionManager = $exceptionManager;
		}

		final public function allowedConsumers ():array {

			return [ServiceCoordinator::class];
		}

		/**
		 * $request = $this->requestFactory->createRequest(GET, $url)
		 * 
		 * $this->requestResponse = $this->client->sendRequest($request)
		*/
		abstract protected function makeRequest ():void;

		abstract protected function translate ():OptionalDTO;

		protected function translationFailure (Throwable $exception):OptionalDTO {

			$this->exceptionManager->queueAlertAdapter($exception, $this->requestResponse);

			return new OptionalDTO($this->requestResponse, false);
		}
	}
?>