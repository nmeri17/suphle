<?php
	namespace Tilwa\IO\Http;

	use Tilwa\Services\InterceptsExternalPayload;

	use Tilwa\Exception\DetectedExceptionManager;

	use Psr\Http\Client\{ClientInterface, ClientExceptionInterface };

	use Psr\Http\Message\RequestFactoryInterface;

	abstract class BaseHttpRequest extends InterceptsExternalPayload {

		protected $client, $requestFactory, $exceptionManager,

		$requestResponse;

		public function __construct (ClientInterface $client, RequestFactoryInterface $requestFactory, DetectedExceptionManager $exceptionManager) {

			$this->client = $client;

			$this->requestFactory = $requestFactory;

			$this->exceptionManager = $exceptionManager;
		}

		/**
		 * $request = $this->requestFactory->createRequest(GET, $url)
		 * 
		 * $this->requestResponse = $this->client->sendRequest($request)
		*/
		abstract protected function setRequestResponse ():void;

		protected function translationFailure (Throwable $exception):void {

			$this->exceptionManager->queueAlertAdapter($exception, $this->requestResponse);

			parent::translationFailure($exception);
		}
	}
?>