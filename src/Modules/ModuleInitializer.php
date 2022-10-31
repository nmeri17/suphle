<?php
	namespace Suphle\Modules;

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Routing\{RouteManager, ExternalRouteMatcher};

	use Suphle\Request\RequestDetails;

	use Suphle\Middleware\MiddlewareQueue;

	use Suphle\Services\DecoratorHandlers\VariableDependenciesHandler;

	use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

	use Suphle\Contracts\Modules\{HighLevelRequestHandler, DescriptorInterface};

	use Suphle\Exception\Explosives\{UnauthorizedServiceAccess, Unauthenticated};
	
	class ModuleInitializer implements HighLevelRequestHandler {

		private $foundRoute = false, $router, $descriptor,

		$container, $indicator, $requestDetails, $finalRenderer,

		$variableDecorator, $externalRouters;

		public function __construct (
			DescriptorInterface $descriptor, RequestDetails $requestDetails,

			VariableDependenciesHandler $variableDecorator,

			RouteManager $router, ExternalRouteMatcher $externalRouters
		) {

			$this->descriptor = $descriptor;

			$this->container = $descriptor->getContainer();

			$this->requestDetails = $requestDetails;

			$this->variableDecorator = $variableDecorator;

			$this->router = $router;

			$this->externalRouters = $externalRouters;
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

		private function bindRoutingSideEffects ():void {

			$this->router->getPlaceholderStorage()

			->exchangeTokenValues($this->requestDetails->getPath()); // thanks to object references, this update affects the object stored in Container without explicitly rebinding

			$renderer = $this->variableDecorator->examineInstance(

				$this->router->getActiveRenderer(), self::class
			);

			$this->container->whenTypeAny()->needsAny([

				BaseRenderer::class => $renderer // any object using this expects its module to have routed to a renderer
			]);
		}

		/**
		 * @param {rendererManager} this manager should come from currently active module
		 * 
		 * @throws UnauthorizedServiceAccess, Unauthenticated, ValidationFailure
		*/
		public function fullRequestProtocols (RoutedRendererManager $rendererManager):self {

			if ($this->externalRouters->hasActiveHandler())

				return $this;

			$this->indicator = $this->router->getIndicator();

			$this->attemptAuthentication()->authorizeRequest();

			$rendererManager->bootCoodinatorManager()

			->mayBeInvalid(); // throws no error if validation Passed

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

		public function getRouter ():RouteManager {
			
			return $this->router;
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
		}

		public function whenActive ():self {

			if (!$this->externalRouters->hasActiveHandler()) // not booting module for external routers since request won't be handled in the module but by a separate app

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
	}
?>