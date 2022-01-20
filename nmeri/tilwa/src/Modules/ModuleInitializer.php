<?php
	namespace Tilwa\Modules;

	use Tilwa\Response\{ResponseManager, Format\AbstractRenderer};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Bridge\Laravel\ModuleRouteMatcher;

	use Tilwa\Contracts\{Auth\AuthStorage, App\HighLevelRequestHandler};

	use Tilwa\Exception\Explosives\{UnauthorizedServiceAccess, Unauthenticated};
	
	class ModuleInitializer implements HighLevelRequestHandler {

		private $router, $descriptor, $responseManager,

		$laravelMatcher, $container, $indicator;

		private $foundRoute = false;

		function __construct(ModuleDescriptor $descriptor) {

			$this->descriptor = $descriptor;

			$this->container = $descriptor->getContainer();
		}

		public function assignRoute():self {

			$this->router->findRenderer();
			
			if ($this->handlingRenderer() ) $this->foundRoute = true;

			else {

				$this->laravelMatcher = $this->container->getClass(ModuleRouteMatcher::class);

				$this->foundRoute = $this->laravelMatcher->canHandleRequest(); // assumes module has booted
			}

			return $this;
		}

		/**
		 * @throws UnauthorizedServiceAccess, Unauthenticated, ValidationFailure
		*/
		public function triggerRequest():string {

			if ($this->isLaravelRoute())

				return $this->laravelMatcher->getResponse();

			$this->setRouteDetailsIndicator();

			$this->responseManager = $this->container->getClass(ResponseManager::class);

			$this->attemptAuthentication()->authorizeRequest();

			$validationPassed = $this->responseManager

			->bootControllerManager()->mayBeInvalid();

			return $this->container->getClass (MiddlewareQueue::class)

			->runStack();
		}

		public function getRouter ():RouteManager {
			
			return $this->router;
		}

		public function getResponseManager ():ResponseManager {
			
			return $this->responseManager;
		}

		public function initialize ():self {

			$this->router = $this->container->getClass (RouteManager::class);

			return $this;
		}

		private function bindContextualGlobals ():void {

			$this->container->whenTypeAny()

			->needsAny([

				ModuleDescriptor::class => $this->descriptor,

				AbstractRenderer::class => $this->handlingRenderer()
			]);
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
		}

		public function whenActive ():self {

			if ($this->isLaravelRoute()) return $this;

			$this->descriptor->prepareToRun();

			return $this;
		}

		/**
		 * If route is secured, confirm user is authenticated. When successful, it'll override the default authStorage method provided
		 * 
		 * @throws Unauthenticated
		*/
		private function attemptAuthentication ():self {

			$authMethod = $this->indicator->activeAuthStorage();

			if (!is_null($authMethod)) {

				if ( !$this->responseManager->requestAuthenticationStatus($authMethod))

					throw new Unauthenticated($authMethod);

				$this->container->whenTypeAny()

				->needsAny([ AuthStorage::class => $authMethod]);
			}

			return $this;
		}

		/**
		 * @throws UnauthorizedServiceAccess
		*/
		private function authorizeRequest ():self {

			if (!$this->indicator->getAuthorizer()->passesActiveRules())

				throw new UnauthorizedServiceAccess;

			return $this;
		}

		public function handlingRenderer ():AbstractRenderer { /* ?AbstractRenderer */

			// if (!$this->isLaravelRoute())

				return $this->router->getActiveRenderer();

			// else createFrom(their response object)
		}

		private function isLaravelRoute ():bool {

			return !is_null($this->laravelMatcher);
		}

		private function setRouteDetailsIndicator ():void {

			$this->indicator = $this->router->getIndicator();
		}
	}
?>