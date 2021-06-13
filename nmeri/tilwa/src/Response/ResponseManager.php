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

		public function handleValidRequest():AbstractRenderer {

			$renderer = $this->renderer;

			$router = $this->router;

			$manager = $this->controllerManager;

			if (!$this->requestDetails->isApiRoute())

				$router->setPrevious($renderer, $manager->getRequest());

			if ($renderer instanceof Markup && $router->acceptsJson())

				$renderer->setWantsJson();

			$manager->updatePlaceholders()

			->hydrateModels($renderer->getRouteMethod());

			$this->runMiddleware(); // called here so some awesome middleware can override default behavior on our booted controller. may imply injecting the manager

			return $renderer->invokeActionHandler($manager->getHandlerParameters());
		}

		public function isValidRequest ():bool {

			return $this->controllerManager->getRequest()->isValidated();
		}

		// middleware CURRENTLY delimited by commas. Middleware parameters delimited by colons
		private function runMiddleware ():bool {

			$passed = true;

			foreach ($this->renderer->getMiddlewares() as $mw ) {

				@[$className, $args] = explode(',', $mw);

				$instance = $this->container->getClass($className);

				if (is_callable($instance->postSourceBehavior))

					$this->responseMutations[] = $instance->postSourceBehavior;

				else $passed = $instance->handle( explode(':', $args) );

				if ( !$passed ) return $passed; // terminate
			}

			return $passed;
		}

		// last action before response is flushed. log or profile middleware goes here
		public function mutateResponse(string $currentBody):string {

			foreach ($this->responseMutations as $handler)

				$currentBody = $handler($currentBody);
			
			return $currentBody;
		}

		public function validateManager():void {

			$globalDependencies = $this->container->getClass(ModuleDescriptor::class)->getDependsOn();

			$this->controllerManager->validateController($globalDependencies);
		}

		public function buildManagerTarget():void {

			$this->controllerManager->bootController()

			->setHandlerParameters($this->renderer->handler)

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

			// routeManager should update a renderer property after the hit
		}

		public function requestAuthenticationStatus (AuthStorage $storage):bool {

			return !is_null($storage->getIdentifier());
		}
	}
?>