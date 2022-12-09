<?php
	namespace Suphle\Modules;

	use Suphle\Hydration\Container;

	use Suphle\Hydration\Structures\{ContainerBooter, BaseInterfaceCollection};

	use Suphle\Modules\Structures\ActiveDescriptors;

	use Suphle\Flows\OuterFlowWrapper;

	use Suphle\Contracts\Config\{AuthContract, Flows as FlowConfig};

	use Suphle\Contracts\{ Auth\ModuleLoginHandler, Presentation\BaseRenderer};

	use Suphle\Contracts\Modules\{HighLevelRequestHandler, DescriptorInterface};

	use Suphle\Events\ModuleLevelEvents;

	use Suphle\Exception\Explosives\{ValidationFailure, NotFoundException};

	use Suphle\Request\RequestDetails;

	use Suphle\Response\RoutedRendererManager;

	use Throwable;

	abstract class ModuleHandlerIdentifier {

		private HighLevelRequestHandler $identifiedHandler;

		private ?DescriptorInterface $routedModule = null;

		protected Container $container;

		protected array $descriptorInstances;

		public function __construct () {

			$this->setTitularContainer();
		}

		protected function setTitularContainer ():void {

			$this->descriptorInstances = $this->getModules();

			if (empty($this->descriptorInstances)) return;

			$this->container = current($this->descriptorInstances)->getContainer(); // we don't bother confirming descriptorInstances has contents since we don't even have access to a default error page to render if it doesn't exist

			$this->container->whenTypeAny()->needsAny([ // for the `bootModules` call

				ActiveDescriptors::class => new ActiveDescriptors($this->descriptorInstances)
			])->setEssentials();
		}
		
		abstract protected function getModules():array;

		public function bootModules ():void {

			$this->container->getClass(ModulesBooter::class)

			->bootAllModules()->prepareFirstModule();
		}

		public function setRequestPath (string $requestPath):void {

			RequestDetails::fromModules(

				$this->descriptorInstances, $requestPath
			);
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

			$container = $this->container;

			if ($container->getClass(FlowConfig::class)->isEnabled()) {

				$wrapper = $container->getClass(OuterFlowWrapper::class);

				if ($wrapper->canHandle())

					return $this->flowRequestHandler($wrapper);
			}

			if ($routedRenderer = $this->handleGenericRequest())

				return $routedRenderer;

			if ( $container->getClass(AuthContract::class)->isLoginRequest())

				return $this->handleLoginRequest();

			throw new NotFoundException;
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

		protected function handleGenericRequest ():?BaseRenderer {

			$moduleRouter = $this->container->getClass(ModuleToRoute::class); // pulling from a container so tests can replace properties on the singleton

			$initializer = $moduleRouter->findContext($this->descriptorInstances);

			if (!$initializer) return null;

			$this->identifiedHandler = $initializer;

			$this->routedModule = $moduleRouter->getActiveModule();

			$initializer->whenActive()->fullRequestProtocols(

				$this->getActiveContainer()->getClass(
				
					RoutedRendererManager::class
				)
			)->setHandlingRenderer();

			return $initializer->handlingRenderer();
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