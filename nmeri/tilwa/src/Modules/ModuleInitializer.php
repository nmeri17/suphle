<?php
	namespace Tilwa\Modules;

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Middleware\MiddlewareQueue;

	use Tilwa\Bridge\Laravel\Routing\ModuleRouteMatcher;

	use Tilwa\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	use Tilwa\Contracts\Modules\{HighLevelRequestHandler, DescriptorInterface};

	use Tilwa\Exception\Explosives\{UnauthorizedServiceAccess, Unauthenticated};
	
	class ModuleInitializer implements HighLevelRequestHandler {

		private $foundRoute = false, $router, $descriptor,

		$laravelMatcher, $container, $indicator,

		$requestDetails, $finalRenderer;

		public function __construct (DescriptorInterface $descriptor, RequestDetails $requestDetails) {

			$this->descriptor = $descriptor;

			$this->container = $descriptor->getContainer();

			$this->requestDetails = $requestDetails;
		}

		public function assignRoute ():self {

			$this->router->findRenderer();
			
			if ($this->router->getActiveRenderer() ) {

				$this->foundRoute = true;

				$this->bindRoutingSideEffects();
			}
			else {

				$this->laravelMatcher = $this->container->getClass(ModuleRouteMatcher::class);

				$this->foundRoute = $this->laravelMatcher->canHandleRequest(); // assumes module has booted
			}

			return $this;
		}

		private function bindRoutingSideEffects ():void {

			$this->router->getPlaceholderStorage()

			->exchangeTokenValues($this->requestDetails->getPath()); // thanks to object references, this update affects the object stored in Container without explicitly rebinding;

			$this->container->whenTypeAny()->needsAny([

				BaseRenderer::class => $this->router->getActiveRenderer() // any object using this expects its module to have routed to a renderer
			]);
		}

		/**
		 * @param {rendererManager} this manager should come from currently active module
		 * 
		 * @throws UnauthorizedServiceAccess, Unauthenticated, ValidationFailure
		*/
		public function fullRequestProtocols (RoutedRendererManager $rendererManager):self {

			if ($this->isLaravelRoute()) return $this;

			$this->indicator = $this->router->getIndicator();

			$this->attemptAuthentication()->authorizeRequest();

			$rendererManager->bootCoodinatorManager()

			->mayBeInvalid(); // throws no error if validation Passed

			return $this;
		}

		public function setHandlingRenderer ():void {

			if ($this->isLaravelRoute())

				$this->finalRenderer = $this->laravelMatcher->convertToRenderer();

			else $this->finalRenderer = $this->container->getClass (MiddlewareQueue::class)

			->runStack();
		}

		public function handlingRenderer ():?BaseRenderer {

			return $this->finalRenderer;
		}

		public function getRouter ():RouteManager {
			
			return $this->router;
		}

		public function prepareToFindRoute ():self {

			$this->router = $this->container->getClass (RouteManager::class);

			return $this;
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
		}

		public function whenActive ():self {

			if (!$this->isLaravelRoute()) // not booting module for external routers since request won't be handled in the module but by a separate app

				$this->descriptor->prepareToRun();

			return $this;
		}

		/**
		 * If route is secured, confirm user is authenticated. When successful, it'll override the default authStorage method provided
		 * 
		 * @throws Unauthenticated
		*/
		protected function attemptAuthentication ():self {

			$routedMechanism = $this->indicator->routedAuthStorage();

			$switchedMechanism = $this->indicator->getProvidedAuthenticator();

			if (!is_null($routedMechanism)) {

				if (!is_null($switchedMechanism))

					$routedMechanism = $switchedMechanism;

				if ( is_null($routedMechanism->getId()))

					throw new Unauthenticated($routedMechanism);
			}
			elseif (!is_null($switchedMechanism))

				$routedMechanism = $switchedMechanism;

			if (!is_null($routedMechanism))

				$this->container->whenTypeAny()

				->needsAny([ AuthStorage::class => $routedMechanism]);

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

		public function isLaravelRoute ():bool {

			return !is_null($this->laravelMatcher);
		}
	}
?>