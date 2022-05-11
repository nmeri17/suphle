<?php
	namespace Tilwa\Modules;

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Middleware\MiddlewareQueue;

	use Tilwa\Bridge\Laravel\Routing\ModuleRouteMatcher;

	use Tilwa\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	use Tilwa\Contracts\Modules\{HighLevelRequestHandler, DescriptorInterface};

	use Tilwa\Exception\Explosives\{UnauthorizedServiceAccess, Unauthenticated};
	
	class ModuleInitializer implements HighLevelRequestHandler {

		private $router, $descriptor, $rendererManager,

		$laravelMatcher, $container, $indicator;

		private $foundRoute = false;

		function __construct(DescriptorInterface $descriptor) {

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

			$this->indicator = $this->router->getIndicator();

			$this->setRendererManager();

			$this->attemptAuthentication()->authorizeRequest();

			$validationPassed = $this->rendererManager

			->bootCoodinatorManager()->mayBeInvalid();

			return $this->container->getClass (MiddlewareQueue::class)

			->runStack();
		}

		public function setRendererManager ():void {

			$this->rendererManager = $this->container->getClass(RoutedRendererManager::class);
		}

		public function getRouter ():RouteManager {
			
			return $this->router;
		}

		public function getRoutedRendererManager ():RoutedRendererManager {
			
			return $this->rendererManager;
		}

		public function initialize ():self {

			$this->router = $this->container->getClass (RouteManager::class);

			return $this;
		}

		private function bindContextualGlobals ():void {

			$this->container->whenTypeAny()->needsAny([

				BaseRenderer::class => $this->handlingRenderer()
			]);
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
		}

		public function whenActive ():self {

			$this->bindContextualGlobals();

			if ($this->isLaravelRoute()) return $this;

			$this->descriptor->prepareToRun();

			return $this;
		}

		/**
		 * If route is secured, confirm user is authenticated. When successful, it'll override the default authStorage method provided
		 * 
		 * @throws Unauthenticated
		*/
		protected function attemptAuthentication ():self {

			$authMethod = $this->indicator->activeAuthStorage();

			if (!is_null($authMethod)) {

				if ( !$this->rendererManager->requestAuthenticationStatus($authMethod))

					throw new Unauthenticated($authMethod);

				$this->container->whenTypeAny()

				->needsAny([ AuthStorage::class => $authMethod]);
			}

			return $this;
		}

		/**
		 * @throws UnauthorizedServiceAccess
		*/
		protected function authorizeRequest ():self {

			if (!$this->indicator->getAuthorizer()->passesActiveRules())

				throw new UnauthorizedServiceAccess;

			return $this;
		}

		public function handlingRenderer ():?BaseRenderer {

			// if (!$this->isLaravelRoute())

				return $this->router->getActiveRenderer();

			// else createFrom(their response object)
		}

		public function isLaravelRoute ():bool {

			return !is_null($this->laravelMatcher);
		}
	}
?>