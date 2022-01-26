<?php
	namespace Tilwa\Response;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Routing\{RouteManager, RequestDetails};

	use Tilwa\Response\Format\{Markup, AbstractRenderer};

	use Tilwa\Controllers\ControllerManager;

	use Tilwa\Contracts\BaseResponseManager;

	use Tilwa\Request\{ValidatorManager, PayloadStorage};

	use Tilwa\Exception\Explosives\ValidationFailure;

	class ResponseManager implements BaseResponseManager {

		private $container, $router, $renderer, $payloadStorage,

		$controllerManager, $flowQueuer;

		function __construct (Container $container, RouteManager $router, ControllerManager $controllerManager, FlowResponseQueuer $flowQueuer, AbstractRenderer $renderer, PayloadStorage $payloadStorage) {

			$this->container = $container;

			$this->router = $router;

			$this->controllerManager = $controllerManager;

			$this->flowQueuer = $flowQueuer;

			$this->renderer = $renderer;

			$this->payloadStorage = $payloadStorage;
		}
		
		public function getResponse ():string {

			return $this->renderer->render();
		}

		public function afterRender():void {

			if ($this->renderer->hasBranches())// the very first request won't be caught in a flow. so, delegate queueing branches

				$this->flowQueuer->insert($this->renderer, $this);
		}

		public function bootControllerManager():self {

			$this->updateControllerManager();

			$this->buildManagerTarget();

			return $this;
		}

		public function handleValidRequest(RequestDetails $requestDetails):AbstractRenderer {

			$renderer = $this->renderer;

			$router = $this->router;

			$manager = $this->controllerManager;

			if (!$requestDetails->isApiRoute())

				$router->setPreviousRenderer($renderer);

			if ($renderer instanceof Markup && $this->payloadStorage->acceptsJson())

				$renderer->setWantsJson();

			$manager->hydrateModels($renderer->getRouteMethod());

			return $renderer->invokeActionHandler($manager->getHandlerParameters());
		}

		public function isValidRequest ():bool {

			return $this->controllerManager->isValidatedRequest();
		}

		public function mayBeInvalid ():void {

			if (!$this->isValidRequest())

				throw new ValidationFailure($this->controllerManager);
		}

		private function buildManagerTarget():void {

			$this->controllerManager->bootController($this->renderer->getHandler())

			->setHandlerParameters()->assignModelsInAction();
		}

		private function updateControllerManager():void {

			$this->controllerManager->setController(

				$this->container->getClass($this->renderer->getController())
			);
		}

		public function getControllerManager():ControllerManager {
			
			return $this->controllerManager;
		}

		public function requestAuthenticationStatus (AuthStorage $storage):bool {

			$storage->resumeSession();

			return !is_null($storage->getUser()); // confirms there's an active session and that its owner exists on the underlying database
		}
	}
?>