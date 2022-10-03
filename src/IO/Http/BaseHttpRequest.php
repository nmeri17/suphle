<?php
	namespace Suphle\IO\Http;

	use Suphle\Services\IndicatesCaughtException;

	use Suphle\Exception\DetectedExceptionManager;

	use Psr\Http\{Client\ClientInterface, Message\ResponseInterface};

	use Throwable;

	abstract class BaseHttpRequest extends IndicatesCaughtException {

		protected $requestClient, $exceptionManager, $httpResponse;

		/**
		 * No need to decorate with VariableDependencies since any request made using external libraries won't use this client at the same time, thus can simply override this constructor
		*/
		public function __construct (ClientInterface $requestClient, DetectedExceptionManager $exceptionManager) {

			$this->requestClient = $requestClient;

			$this->exceptionManager = $exceptionManager;
		}

		/**
		 * {@inheritdoc}
		*/
		public function getDomainObject () {

			try {
				
				return $this->convertToDomainObject(

					$this->httpResponse = $this->getHttpResponse() // saving it in this property for use in error handler without resending request
				);
			}
			catch (Throwable $exception) {

				$this->exception = $exception;
				
				return $this->translationFailure();
			}
		}

		/**
		 * @throws Throwable, when it meets an unexpected/undesirable payload
		*/
		abstract protected function convertToDomainObject (ResponseInterface $response);

		/**
		 * return $this->client->request(GET, $url, $options)
		*/
		abstract protected function getHttpResponse ():ResponseInterface;

		/**
		 * Extra layer of abstraction for it to be replaceable in tests
		*/
		abstract public function getRequestUrl ():string;

		protected function translationFailure ():void {

			$this->exceptionManager->queueAlertAdapter(

				$this->exception, $this->httpResponse
			);

			parent::translationFailure();
		}

		/**
		 * For debugging
		*/
		public function getException ():Throwable {

			return $this->exception;
		}
	}
?>