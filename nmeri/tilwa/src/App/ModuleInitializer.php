<?php

	namespace Tilwa\App;

	use Tilwa\Response\ResponseManager;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Bridge\Laravel\ModuleRouteMatcher;
	
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

			$manager = $this->responseManager;

			$manager->bootControllerManager()

			->assignValidRenderer(); // can set response status codes (on http_response_header or something) here based on this guy's evaluation and renderer type

			$validationPassed = !$manager->rendererValidationFailed();

			if ($validationPassed)

				$manager->handleValidRequest();

			$preliminary = $manager->getResponse();

			if ($validationPassed)
				
				$preliminary = $manager->mutateResponse($preliminary); // those middleware should only get the response object/headers, not our payload

			$manager->afterRender();

			return $preliminary;
		}

		public function getRouter():RouteManager {
			
			return $this->router;
		}

		public function getResponseManager():ResponseManager {
			
			return $this->responseManager;
		}

		public function initialize():self {

			$this->router = new RouteManager($this->descriptor, $this->container);

			$this->bindDefaultObjects();

			$this->responseManager = $this->container->getClass(ResponseManager::class);

			return $this;
		}

		private function bindDefaultObjects():void {

			$this->container->whenTypeAny()

			->needsAny([

				ModuleDescriptor::class => $this->descriptor, // all requests for the parent should respond with the active module

				RouteManager::class => $this->router
			]);
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
		}

		public function whenActive ():self {

			if (!is_null($this->laravelMatcher))

				return $this;

			$descriptor = $this->descriptor;

			$this->container->setConfigs($descriptor->getConfigs());

			$customBindings = $this->container->getMethodParameters("entityBindings", $descriptor);

			call_user_func_array([$descriptor, "entityBindings"], $customBindings);

			return $this;
		}
	}
?>