<?php

	namespace Tilwa\App;

	use Tilwa\Response\{ResponseManager, Format\AbstractRenderer};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Bridge\Laravel\ModuleRouteMatcher;

	use Tilwa\Contracts\AuthStorage;

	use Tilwa\Request\PathAuthorizer;

	use Tilwa\Errors\{UnauthorizedServiceAccess, Unauthenticated};
	
	class ModuleInitializer {

		private $router, $descriptor, $responseManager,

		$laravelMatcher, $container;

		private $foundRoute = false;

		function __construct(ModuleDescriptor $descriptor) {

			$this->descriptor = $descriptor;

			$this->container = $descriptor->getContainer();
		}

		public function assignRoute():self {
			
			if ($target = $this->router->findRenderer() ) {

				$this->router->setActiveRenderer($target);

				$this->foundRoute = true;
			}

			else {

				$this->laravelMatcher = $this->container->getClass(ModuleRouteMatcher::class);

				$this->foundRoute = $this->laravelMatcher->canHandleRequest(); // assumes module has booted
			}

			return $this;
		}

		public function triggerRequest():string {

			if (!is_null($this->laravelMatcher))

				return $this->laravelMatcher->getResponse();

			$this->attemptAuthentication()->authorizePath();

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

			$this->container->setConfigs($this->descriptor->getConfigs());

			$this->router = $this->container->getClass (RouteManager::class);

			$this->bindDefaultObjects();

			$this->responseManager = $this->container->getClass(ResponseManager::class);

			return $this;
		}

		private function bindDefaultObjects():void {

			$this->container->whenTypeAny()

			->needsAny([

				ModuleDescriptor::class => $this->descriptor, // all requests for the parent should respond with the active module

				RouteManager::class => $this->router,

				AbstractRenderer::class => $this->router->getActiveRenderer()
			]);
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
		}

		public function whenActive ():self {

			if (!is_null($this->laravelMatcher))

				return $this;

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

			$manager = $this->responseManager;

			if ($authMethod = $manager->patternAuthentication()) {

				if ( !$manager->requestAuthenticationStatus($authMethod))

					throw new Unauthenticated;

				$this->container->whenTypeAny()

				->needsAny([ AuthStorage::class => $authMethod]);
			}

			return $this;
		}

		private function authorizeRequest ():self {

			$authorizer = $this->container->getClass(PathAuthorizer::class);

			foreach ($authorizer->getActiveRules() as $rule)

				if (!$rule->permit())

					throw new UnauthorizedServiceAccess;

			return $this;
		}
	}
?>