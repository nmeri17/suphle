<?php
	namespace Tilwa\Modules;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Contracts\Auth\{ModuleLoginHandler, Config\AuthContract};

	use Tilwa\Contracts\Modules\DescriptorInterface;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Exception\Explosives\{ValidationFailure, NotFoundException};

	abstract class ModuleHandlerIdentifier {

		private $container, $identifiedHandler, $routedModule,

		$authConfig;

		public function __construct () {

			$this->container = $this->firstModule()->getContainer();

			$this->container->provideSelf();
		}

		protected function firstModule ():DescriptorInterface {

			return current($this->getModules());
		}
		
		abstract protected function getModules():array;

		public function bootModules ():void {

			(new ModulesBooter(
				$this->getModules(), $this->getEventConnector()
			))->boot();
		}

		protected function getEventConnector ():ModuleLevelEvents {

			return new ModuleLevelEvents($this->getModules());
		}

		public function diffusedRequestResponse ():string {

			$exceptionBridge = $this->freshExceptionBridge();

			$exceptionBridge->epilogue();

			try {

				$content = $this->respondFromHandler();
			}
			catch (Throwable $exception) {

				$this->identifiedHandler = $exceptionBridge = $this->freshExceptionBridge();

				$exceptionBridge->hydrateHandler($exception);

				$content = $exceptionBridge->handlingRenderer()->render();
			}

			$this->transferHeaders();

			return $content;
		}
		
		/**
		 * Each of the request handlers should update this class with the underlying renderer they're pulling a response from
		*/
		protected function respondFromHandler ():string {

			if ( $this->authConfig->isLoginRequest())

				return $this->handleLoginRequest();

			$wrapper = $this->container->getClass(OuterFlowWrapper::class);

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			return $this->handleGenericRequest();
		}

		protected function handleGenericRequest ():string {

			$moduleRouter = $this->container->getClass(ModuleToRoute::class); // pulling from a container so tests can replace properties on the singleton

			$initializer = $moduleRouter->findContext($this->getModules());

			if ($initializer) {

				$this->identifiedHandler = $initializer;

				$this->routedModule = $moduleRouter->getActiveModule();

				return $initializer->whenActive()->triggerRequest();
			}

			throw new NotFoundException;
		}

		protected function flowRequestHandler(OuterFlowWrapper $wrapper):string {

			$this->identifiedHandler = $wrapper;

			$wrapper->setModules($this->getModules());

			$response = $wrapper->getResponse();
			
			$wrapper->afterRender($response);

			$wrapper->emptyFlow();

			return $response;
		}

		public function extractFromContainer ():void {

			$this->authConfig = $this->container->getClass(AuthContract::class);
		}

		public function handleLoginRequest ():string {

			$loginHandler = $this->container->getClass(ModuleLoginHandler::class);

			if (!$loginHandler->isValidRequest())

				throw new ValidationFailure($loginHandler);

			$this->identifiedHandler = $loginHandler;

			return $loginHandler->getResponse();
		}

		public function underlyingRenderer ():AbstractRenderer {

			return $this->identifiedHandler->handlingRenderer();
		}

		protected function transferHeaders ():void {

			$renderer = $this->underlyingRenderer();

			http_response_code($renderer->getStatusCode());

			foreach ($renderer->getHeaders() as $name => $value)

				header("$name: $value");
		}

		private function freshExceptionBridge ():ModuleExceptionBridge {

			if (!is_null($this->routedModule))

				$container = $this->routedModule->getContainer();

			$container = $this->container;

			return $container->getClass(ModuleExceptionBridge::class);
		}
	}
?>