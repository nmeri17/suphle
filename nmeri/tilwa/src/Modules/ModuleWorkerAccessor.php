<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Queues\Adapter as QueueAdapter};

	use Tilwa\Adapters\Queues\BoltDbQueue;

	use Spiral\RoadRunner\{Worker, Http\PSR7Worker, Environment\Mode};

	use Nyholm\Psr7\Factory\Psr17Factory;

	use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

	use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

	use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

	use Throwable;

	/**
	 * RoadRunner will spin this up multiple times for each worker it has to create to service a request type
	*/
	class ModuleWorkerAccessor {

		private $handlerIdentifier, $httpWorker, $queueWorker, $mode;

		public function __construct (ModuleHandlerIdentifier $handlerIdentifier) {

			$this->handlerIdentifier = $handlerIdentifier;
		}

		public function setWorkerMode (string $mode):void {

			$this->mode = $mode;
		}

		protected function isTaskMode ():bool {

			return $this->mode === Mode::MODE_JOBS;
		}

		public function setActiveWorker ():self {

			if ($this->isTaskMode())

				$this->queueWorker = $this->handlerIdentifier->firstContainer()

				->getClass(QueueAdapter::class);

			else {

				$psrFactory = new Psr17Factory;

				$this->httpWorker = new PSR7Worker(

					Worker::create(), $psrFactory, $psrFactory, $psrFactory
				);
			}
		}

		public function buildIdentifier ():self {

			$this->handlerIdentifier->bootModules();

			$this->handlerIdentifier->extractFromContainer();

			return $this;
		}

		public function openEventLoop ():void {

			if ($this->isTaskMode())

				$this->queueWorker->processTasks();

			else $this->processHttpTasks();
		}

		protected function processHttpTasks ():void {

			while ($newRequest = $this->httpWorker->waitRequest()) {

				try {

					$this->flushResponse($newRequest);
				}
				catch (Throwable $exception) { // only roadRunner specific errors are expected here, since our own errors are fully handled internally

					$this->httpWorker->getWorker()

					->error($exception->getMessage());
				}
			}
		}

		protected function flushResponse (?ServerRequestInterface $incomingRequest):void {

			$this->handlerIdentifier->setRequestPath(

				$incomingRequest->getRequestTarget()
			); // this depends on stdInputReader, so it's assumed that headers are equally set, possibly from here

			$this->handlerIdentifier->diffuseSetResponse(false);

			$psrResponse = $this->getPsrResponse(

				$this->handlerIdentifier->underlyingRenderer()
			);

			$this->httpWorker->respond($psrResponse);
		}

		protected function getPsrResponse (BaseRenderer $renderer):ResponseInterface {

			$symfonyResponse = new SymfonyResponse(

				$renderer->render(), $renderer->getStatusCode(),

				$renderer->getHeaders()
			);
			
			$psr17Factory = new Psr17Factory;

			$psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

			return $psrHttpFactory->createResponse($symfonyResponse);
		}
	}
?>