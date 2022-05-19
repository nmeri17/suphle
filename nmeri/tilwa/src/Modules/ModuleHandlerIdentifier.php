<?php
	namespace Tilwa\Modules;

	use Tilwa\Hydration\Container;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Contracts\{Modules\DescriptorInterface, Config\AuthContract, Auth\ModuleLoginHandler, Presentation\BaseRenderer};

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Exception\Explosives\{ValidationFailure, NotFoundException};

	use Tilwa\Request\RequestDetails;

	use Throwable;

	abstract class ModuleHandlerIdentifier {

		private $identifiedHandler, $routedModule, $authConfig;

		protected $container;

		public function __construct () {

			$this->container = current($this->getModules())->getContainer();

			$this->container->provideSelf();
		}
		
		abstract protected function getModules():array;

		public function bootModules ():void {

			(new ModulesBooter(
				$this->getModules(), $this->getEventConnector()
			))
			->bootAll()->prepareFirstModule();
		}

		protected function getEventConnector ():ModuleLevelEvents {

			return new ModuleLevelEvents($this->getModules());
		}

		public function setRequestPath (string $requestPath):void {

			RequestDetails::fromModules($this->getModules(), $requestPath);
		}

		public function diffusedRequestResponse ():string {

			$this->freshExceptionBridge()->epilogue();

			try {

				$renderer = $this->respondFromHandler();
			}
			catch (Throwable $exception) {

				$renderer = $this->findExceptionRenderer($exception);
			}

			$this->transferHeaders();

			return $renderer->render();
		}

		private function freshExceptionBridge ():ModuleExceptionBridge {

			return $this->getActiveContainer()->getClass(ModuleExceptionBridge::class);
		}
		
		/**
		 * Each of the request handlers should update this class with the underlying renderer they're pulling a response from
		*/
		protected function respondFromHandler ():BaseRenderer {

			if ( $this->authConfig->isLoginRequest())

				return $this->handleLoginRequest();

			$wrapper = $this->container->getClass(OuterFlowWrapper::class);

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			return $this->handleGenericRequest();
		}

		public function handleLoginRequest ():BaseRenderer {

			$loginHandler = $this->getLoginHandler();

			if (!$loginHandler->isValidRequest())

				throw new ValidationFailure($loginHandler);

			$this->identifiedHandler = $loginHandler;

			$loginHandler->setResponseRenderer()->processLoginRequest();

			return $loginHandler->handlingRenderer();
		}

		public function getLoginHandler ():ModuleLoginHandler {

			return $this->container->getClass(ModuleLoginHandler::class);
		}

		protected function handleGenericRequest ():BaseRenderer {

			$moduleRouter = $this->container->getClass(ModuleToRoute::class); // pulling from a container so tests can replace properties on the singleton

			$initializer = $moduleRouter->findContext($this->getModules());

			if ($initializer) {

				$this->identifiedHandler = $initializer;

				$this->routedModule = $moduleRouter->getActiveModule();

				$initializer->whenActive()->fullRequestProtocols()

				->setHandlingRenderer();

				return $initializer->handlingRenderer();
			}

			throw new NotFoundException;
		}

		public function flowRequestHandler (OuterFlowWrapper $wrapper):BaseRenderer {

			$this->identifiedHandler = $wrapper;

			$renderer = $wrapper->handlingRenderer();
			
			$wrapper->afterRender($renderer->render());

			$wrapper->emptyFlow();

			return $renderer;
		}

		public function findExceptionRenderer (Throwable $exception):BaseRenderer {

			$exceptionBridge = $this->identifiedHandler = $this->freshExceptionBridge(); // from currently active container after routing may have occured

			$exceptionBridge->hydrateHandler($exception);

			$renderer = $exceptionBridge->handlingRenderer();

			$renderer->hydrateDependencies($this->container);

			return $renderer;
		}

		public function extractFromContainer ():void {

			$this->authConfig = $this->container->getClass(AuthContract::class);
		}

		public function underlyingRenderer ():BaseRenderer {

			return $this->identifiedHandler->handlingRenderer();
		}

		protected function transferHeaders ():void {

			$renderer = $this->underlyingRenderer();

			http_response_code($renderer->getStatusCode());

			foreach ($renderer->getHeaders() as $name => $value)

				header("$name: $value");
		}

		protected function getActiveContainer ():Container {

			if (!is_null($this->routedModule))

				return $this->routedModule->getContainer();

			return $this->container;
		}
	}
?>