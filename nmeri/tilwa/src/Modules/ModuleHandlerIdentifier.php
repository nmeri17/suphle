<?php
	namespace Tilwa\Modules;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Contracts\{Config\Auth as AuthConfig, Auth\LoginRenderers};

	use Tilwa\Auth\LoginRequestHandler;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Exception\Explosives\ValidationFailure;

	abstract class ModuleHandlerIdentifier {

		private $requestDetails, $container, $authConfig, $identifiedHandler, $routedModule;

		public function __construct () {

			$this->container = current($this->getModules())->getContainer();

			$this->container->provideSelf();
		}
		
		/**
		 * Not all modules should go here. Only those expected to contain routes
		*/
		abstract protected function getModules():array;
		
		public function orchestrate():string {

			$this->extractFromContainer();

			$modules = $this->getModules();

			$bootStarter = new ModulesBooter($modules, new ModuleLevelEvents($modules));

			$bootStarter->boot();

			try {

				$content = $this->beginRequest();
			}
			catch (Throwable $exception) {

				$this->identifiedHandler = $bridge = new ModuleExceptionBridge($this->getActiveContainer());

				$bridge->hydrateHandler($exception);

				$content = $bridge->getResponse();
			}

			$this->transferHeaders();

			return $content;
		}
		
		/**
		 * Each of the request handlers should update this class with the underlying renderer they're pulling a response from
		*/
		protected function beginRequest ():string {

			$modules = $this->getModules();

			if ($this->requestDetails->isPostRequest() && $rendererName = $this->getLoginCollection())

				return $this->handleLoginRequest($rendererName);

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

			$this->requestDetails = $this->container->getClass(RequestDetails::class);

			$this->authConfig = $this->container->getClass(AuthConfig::class);
		}

		/**
		 * @return A Tilwa\Contracts\LoginRenderers::class when the incoming path matches one of the login paths configured
		*/
		private function getLoginCollection ():?string {

			return $this->authConfig->getPathRenderer($this->requestDetails->getPath());
		}

		protected function handleLoginRequest (string $collectionName):string {

			$handler = $this->getLoginHandler($collectionName);

			$handler->setAuthService();

			$this->identifiedHandler = $handler;

			if (!$handler->isValidRequest())

				throw new ValidationFailure($handler);

			return $handler->getResponse();
		}

		protected function getLoginHandler (string $collectionName):LoginRequestHandler {

			$container = $this->container;

			$handlerName = LoginRequestHandler::class;

			$collection = $container->getClass($collectionName);

			return $container->whenType($handlerName)

			->needsArguments(compact("collection"))

			->getClass($handlerName);
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

		private function getActiveContainer ():Container {

			if (!is_null($this->routedModule))

				return $this->routedModule->getContainer();

			return $this->container;
		}
	}
?>