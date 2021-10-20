<?php

	namespace Tilwa\App;

	use Tilwa\Response\{ResponseManager, Format\AbstractRenderer};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Bridge\Laravel\ModuleRouteMatcher;

	use Tilwa\Contracts\{Auth\AuthStorage, App\HighLevelRequestHandler};

	use Tilwa\Errors\{UnauthorizedServiceAccess, Unauthenticated};
	
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

		public function triggerRequest():string {

			if ($this->isLaravelRoute())

				return $this->laravelMatcher->getResponse();

			$this->setRouteDetailsIndicator();

			$this->attemptAuthentication()->authorizeRequest();

			$validationPassed = $this->responseManager

			->bootControllerManager()->isValidRequest();

			if (!$validationPassed)

				throw new ValidationFailure;

			return $this->container->getClass (MiddlewareQueue::class)

			->runStack();
		}

		public function getRouter():RouteManager {
			
			return $this->router;
		}

		public function getResponseManager():ResponseManager {
			
			return $this->responseManager;
		}

		public function initialize():self {

			$this->container->provideSelf();

			$this->router = $this->container->getClass (RouteManager::class);

			$this->bindDefaultObjects();

			$this->responseManager = $this->container->getClass(ResponseManager::class);

			return $this;
		}

		private function bindDefaultObjects():void {

			$this->container->whenTypeAny()

			->needsAny([

				ModuleDescriptor::class => $this->descriptor,

				RouteManager::class => $this->router,

				AbstractRenderer::class => $this->handlingRenderer()
			]);
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
		}

		public function whenActive ():self {

			if ($this->isLaravelRoute()) return $this;

			$descriptor = $this->descriptor;

			$customBindings = $this->container->getMethodParameters("entityBindings", $descriptor);

			$descriptor->entityBindings(...$customBindings);

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

					throw new Unauthenticated;

				$this->container->whenTypeAny()

				->needsAny([ AuthStorage::class => $authMethod]);
			}

			return $this;
		}

		private function authorizeRequest ():self {

			$authorizer = $this->indicator->getAuthorizer();

			foreach ($authorizer->getActiveRules() as $rule)

				if (!$rule->permit())

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