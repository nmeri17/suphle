<?php
	namespace Suphle\Modules;

	use Suphle\Hydration\Container;

	use Suphle\Flows\OuterFlowWrapper;

	use Suphle\Contracts\Config\{AuthContract, Flows as FlowConfig};

	use Suphle\Contracts\{Modules\DescriptorInterface, Auth\ModuleLoginHandler, Presentation\BaseRenderer};

	use Suphle\Events\ModuleLevelEvents;

	use Suphle\Exception\Explosives\{ValidationFailure, NotFoundException};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\RoutedRendererManager;

	use Throwable;

	abstract class ModuleHandlerIdentifier {

		private $identifiedHandler, $routedModule, $authConfig,

		$flowConfig;

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
			->bootAllModules()->prepareFirstModule();
		}

		protected function getEventConnector ():ModuleLevelEvents {

			return new ModuleLevelEvents($this->getModules());
		}

		public function setRequestPath (string $requestPath):void {

			RequestDetails::fromModules($this->getModules(), $requestPath);
		}

		/**
		 * @param {writeHeaders}:bool. When false, we assume response is not being outputted to browser or is piped to another process that will write them
		*/
		public function diffuseSetResponse (bool $writeHeaders = true):void {

			$this->freshExceptionBridge()->epilogue();

			try {

				$this->respondFromHandler();
			}
			catch (Throwable $exception) {

				$this->findExceptionRenderer($exception);
			}

			if ($writeHeaders) $this->transferHeaders();
		}

		private function freshExceptionBridge ():ModuleExceptionBridge {

			return $this->getActiveContainer()->getClass(ModuleExceptionBridge::class);
		}
		
		/**
		 * Each of the request handlers should update this class with the underlying renderer they're pulling a response from
		*/
		public function respondFromHandler ():BaseRenderer {

			if ( $this->authConfig->isLoginRequest())

				return $this->handleLoginRequest();

			if ($this->flowConfig->isEnabled()) {

				$wrapper = $this->container->getClass(OuterFlowWrapper::class);

				if ($wrapper->canHandle())

					return $this->flowRequestHandler($wrapper);
			}

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

				$rendererManager = $this->routedModule->getContainer()->getClass(RoutedRendererManager::class);

				$initializer->whenActive()

				->fullRequestProtocols($rendererManager)

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

			$exceptionBridge->successfullyHandled();

			return $renderer;
		}

		public function extractFromContainer ():void {

			$this->authConfig = $this->container->getClass(AuthContract::class);

			$this->flowConfig = $this->container->getClass(FlowConfig::class);
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

		public function firstContainer ():Container {

			return $this->container;
		}
	}
?>