<?php

	namespace Tilwa\Response;

	use Tilwa\App\{Container, ModuleDescriptor};

	use Tilwa\Routing\RouteManager;

	use Tilwa\Response\Format\{Markup, AbstractRenderer};

	use Tilwa\Controllers\ControllerManager;

	use Tilwa\Contracts\BaseResponseManager;

	class ResponseManager implements BaseResponseManager {

		private $container, $router, $renderer,

		$controllerManager, $requestDetails,

		$flowQueuer;

		public $responseMutations = [];

		function __construct (Container $container, RouteManager $router, ControllerManager $controllerManager, RequestDetails $requestDetails, FlowResponseQueuer $flowQueuer, AbstractRenderer $renderer) {

			$this->container = $container;

			$this->router = $router;

			$this->controllerManager = $controllerManager;

			$this->requestDetails = $requestDetails;

			$this->flowQueuer = $flowQueuer;

			$this->renderer = $renderer;
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

			$this->validateManager();

			$this->buildManagerTarget();

			return $this;
		}

		public function handleValidRequest(BaseRequest $request):AbstractRenderer {

			$renderer = $this->renderer;

			$router = $this->router;

			$manager = $this->controllerManager;

			if (!$this->requestDetails->isApiRoute())

				$router->setPrevious($renderer, $request);

			if ($renderer instanceof Markup && $router->acceptsJson())

				$renderer->setWantsJson();

			$manager->updateRequest($request)

			->hydrateModels($renderer->getRouteMethod());

			return $renderer->invokeActionHandler($manager->getHandlerParameters());
		}

		public function isValidRequest ():bool {

			return $this->controllerManager->getRequest()->isValidated();
		}

		public function validateManager():void {

			$globalDependencies = $this->container->getClass(ModuleDescriptor::class)->getDependsOn();

			$this->controllerManager->validateController($globalDependencies);
		}

		public function buildManagerTarget():void {

			$this->controllerManager->bootController()

			->setHandlerParameters($this->renderer->getHandler())

			->assignActionRequest() // this should run before model hydration and before validation

			->assignModelsInAction();
		}

		private function updateControllerManager():void {

			$this->controllerManager->setController(

				$this->container->getClass($this->renderer->getController())
			);
		}

		public function getControllerManager():ControllerManager {
			
			return $this->controllerManager;
		}

		public function patternAuthentication ():AuthStorage {

			return $this->router->getPatternAuthentication();
		}

		public function requestAuthenticationStatus (AuthStorage $storage):bool {

			$storage->resumeSession();

			return !is_null($storage->getUser()); // confirms there's an active session and that its owner exists on the underlying database
		}
	}
?>