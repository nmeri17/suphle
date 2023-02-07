<?php
	namespace Suphle\Modules;

	use Suphle\Routing\{RouteManager, ExternalRouteMatcher, PatternIndicator};

	use Suphle\Request\RequestDetails;

	use Suphle\Middleware\MiddlewareQueue;

	use Suphle\Hydration\{Container, DecoratorHydrator};

	use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer, Response\RendererManager};

	use Suphle\Contracts\Modules\{HighLevelRequestHandler, DescriptorInterface};

	use Suphle\Exception\Explosives\{UnauthorizedServiceAccess, Unauthenticated};
	
	class ModuleInitializer implements HighLevelRequestHandler {

		protected bool $foundRoute = false;

		protected readonly Container $container;

		protected PatternIndicator $indicator;

		protected ?BaseRenderer $finalRenderer = null;

		public function __construct (
			protected readonly DescriptorInterface $descriptor,

			protected readonly RequestDetails $requestDetails,

			protected readonly DecoratorHydrator $decoratorHydrator,

			protected readonly RouteManager $router,

			protected readonly ExternalRouteMatcher $externalRouters
		) {

			$this->container = $descriptor->getContainer();
		}

		public function assignRoute ():self {

			$this->router->findRenderer();
			
			if ($this->router->getActiveRenderer() ) {

				$this->foundRoute = true;

				$this->bindRoutingSideEffects();
			}
			
			else $this->foundRoute = $this->externalRouters->shouldDelegateRouting();

			return $this;
		}

		protected function bindRoutingSideEffects ():void {

			$this->router->getPlaceholderStorage()

			->exchangeTokenValues($this->requestDetails->getPath()); // thanks to object references, this update affects the object stored in Container without explicitly rebinding

			$renderer = $this->router->getActiveRenderer();

			/**
			 * Not really necessary but just a slight optimization to save callers from demeter on the router.
			 * 
			 * Any of those callers should assume its module has routed to a renderer
			 * 
			 * Ordering here binds to container before scoping the renderer, in case any of the dependencies requires the renderer itself
			*/
			$this->container->whenTypeAny()->needsAny([

				BaseRenderer::class => $renderer
			]);

			$this->decoratorHydrator->scopeInjecting( // this is where all renderer dependencies are being injected

				$renderer, self::class
			);
		}

		/**
		 * @param {rendererManager} this manager should come from currently active module
		 * 
		 * @throws UnauthorizedServiceAccess, Unauthenticated, ValidationFailure
		*/
		public function fullRequestProtocols (RendererManager $rendererManager):self {

			if ($this->externalRouters->hasActiveHandler())

				return $this;

			$this->indicator = $this->router->getIndicator();

			$this->attemptAuthentication()->authorizeRequest();

			$rendererManager->mayBeInvalid()->bootDefaultRenderer();

			return $this;
		}

		public function setHandlingRenderer ():void {

			if ($this->externalRouters->hasActiveHandler())

				$this->finalRenderer = $this->externalRouters->getConvertedRenderer();

			else $this->finalRenderer = $this->container->getClass (MiddlewareQueue::class)

			->runStack();
		}

		public function handlingRenderer ():?BaseRenderer {

			return $this->finalRenderer;
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
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
	}
?>