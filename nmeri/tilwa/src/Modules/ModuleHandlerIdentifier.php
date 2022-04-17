<?php
	namespace Tilwa\Modules;

	use Tilwa\Hydration\Container;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Contracts\{Modules\DescriptorInterface, Config\AuthContract, Auth\ModuleLoginHandler, Presentation\BaseRenderer};

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Exception\Explosives\{ValidationFailure, NotFoundException};

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

		public function diffusedRequestResponse ():string {

			$exceptionBridge = $this->freshExceptionBridge();

			$exceptionBridge->epilogue();

			try {

				$content = $this->respondFromHandler();
			}
			catch (Throwable $exception) {

				$this->identifiedHandler = $exceptionBridge = $this->freshExceptionBridge();

				$exceptionBridge->hydrateHandler($exception);

				$renderer = $exceptionBridge->handlingRenderer();

				$renderer->hydrateDependencies($this->container);

				$content = $renderer->render();
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

		public function flowRequestHandler(OuterFlowWrapper $wrapper):string {

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

			$loginHandler = $this->getLoginHandler();

			if (!$loginHandler->isValidRequest())

				throw new ValidationFailure($loginHandler);

			$this->identifiedHandler = $loginHandler;

			return $loginHandler->getResponse();
		}

		public function getLoginHandler ():ModuleLoginHandler {

			return $this->container->getClass(ModuleLoginHandler::class);
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

		private function freshExceptionBridge ():ModuleExceptionBridge {

			return $this->getActiveContainer()->getClass(ModuleExceptionBridge::class);
		}
	}
?>