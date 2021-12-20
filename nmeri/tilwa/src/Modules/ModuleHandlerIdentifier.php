<?php
	namespace Tilwa\Modules;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\Errors\ExceptionRenderer;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Contracts\{Config\Auth as AuthConfig, Auth\LoginRenderers};

	use Tilwa\Auth\LoginRequestHandler;

	use Tilwa\Response\Format\AbstractRenderer;

	abstract class ModuleHandlerIdentifier {

		private $requestDetails, $container, $authConfig, $identifiedHandler;

		public function __construct () {

			$this->container = current($this->getModules())->getContainer();

			$this->container->provideSelf();
		}
		
		abstract protected function getModules():array;
		
		public function orchestrate():string {

			$this->extractFromContainer();

			(new ModulesBooter($this->getModules()))->boot();

			new ExceptionRenderer($this->getErrorHandlers(), $this->container);

			$content = $this->beginRequest();

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

			$initializer = $this->container->getClass(ModuleToRoute::class) // pulling from a container so tests can replace properties on the singleton

			->findContext($modules);

			if ($initializer) {

				$this->identifiedHandler = $initializer;

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

		protected function getErrorHandlers ():array {

			return [
				NotFoundException::class => "handler",

				Unauthenticated::class => "handler",

				ValidationFailure::class => "handler",

				IncompatibleHttpMethod::class => "handler"
			];
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

			if (!$handler->isValidRequest())

				throw new ValidationFailure;

			$this->identifiedHandler = $handler;

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
	}
?>