<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Spiral\RoadRunner\{Worker, Http\PSR7Worker};

	use Nyholm\Psr7\Factory\Psr17Factory;

	use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

	use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

	use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};

	use Throwable;

	class ModuleWorkerAccessor {

		private $handlerIdentifier, $roadRunner;

		public function __construct (ModuleHandlerIdentifier $handlerIdentifier) {

			$this->handlerIdentifier = $handlerIdentifier;
			
			$psrFactory = new Psr17Factory;

			$this->roadRunner = new PSR7Worker(

				Worker::create(), $psrFactory, $psrFactory, $psrFactory
			);
		}

		public function onStart ():self {

			$this->handlerIdentifier->bootModules();

			$this->handlerIdentifier->extractFromContainer();

			return $this;
		}

		public function acceptRequests ():void {

			while ($newRequest = $this->roadRunner->waitRequest()) {

				try {

					$this->flushResponse($newRequest);
				}
				catch (Throwable $exception) { // only roadRunner specific errors are expected here, since our own errors are fully handled internally

					$this->roadRunner->getWorker()

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

			$this->roadRunner->respond($psrResponse);
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