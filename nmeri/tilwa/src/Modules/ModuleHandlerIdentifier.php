<?php
	namespace Tilwa\Modules;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Contracts\Auth\ModuleLoginHandler;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Exception\Explosives\{ValidationFailure, NotFoundException};

	abstract class ModuleHandlerIdentifier {

		private $container, $identifiedHandler, $routedModule,

		$loginHandler;

		public function __construct () {

			$this->container = current($this->getModules())->getContainer();

			$this->container->provideSelf();

			$this->extractFromContainer();
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

			if ( $this->loginHandler->isLoginRequest())

				return $this->handleLoginRequest();

			$modules = $this->getModules();

			$wrapper = $this->getFlowWrapper($modules);

			if ($wrapper->canHandle())

				return $this->flowRequestHandler($wrapper);

			return $this->handleGenericRequest($modules);
		}

		protected function handleGenericRequest (array $modules):string {

			$moduleRouter = $this->container->getClass(ModuleToRoute::class); // pulling from a container so tests can replace properties on the singleton

			$initializer = $moduleRouter->findContext($modules);

			if ($initializer) {

				$this->identifiedHandler = $initializer;

				$this->routedModule = $moduleRouter->getActiveModule();

				return $initializer->whenActive()->triggerRequest();
			}

			throw new NotFoundException;
		}

		protected function flowRequestHandler(OuterFlowWrapper $wrapper):string {

			$this->identifiedHandler = $wrapper;

			$response = $wrapper->getResponse();
			
			$wrapper->afterRender($response);

			$wrapper->emptyFlow();

			return $response;
		}

		private function getFlowWrapper (array $modules):OuterFlowWrapper {

			$wrapperName = OuterFlowWrapper::class;

			return $this->container->whenType($wrapperName)

			->needsArguments([

				"modules" => $modules
			])

			->getClass($wrapperName);
		}

		private function extractFromContainer ():void {

			$this->loginHandler = $this->container->getClass(ModuleLoginHandler::class);
		}

		protected function handleLoginRequest ():string {

			if (!$this->loginHandler->isValidRequest())

				throw new ValidationFailure($this->loginHandler);

			$this->identifiedHandler = $this->loginHandler;

			return $this->loginHandler->getResponse();
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