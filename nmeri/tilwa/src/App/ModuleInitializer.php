<?php

	namespace Tilwa\App;

	use Tilwa\Http\Response\ResponseManager;

	use Tilwa\Routing\RouteManager;
	
	class ModuleInitializer {

		private $router, $descriptor, $responseManager,

		$requestPath, $requestMethod;

		private $foundRoute = false;

		function __construct(ModuleDescriptor $descriptor, string $requestPath, string $requestMethod) {

			$this->descriptor = $descriptor;

			$this->requestPath = $requestPath;
			
			$this->requestMethod = $requestMethod;
		}

		public function assignRoute():self {
			
			if ($target = $this->router->findRenderer() ) {

				$this->router->setActiveRenderer($target)->savePayload();

				$this->foundRoute = true;
			}
			return $this;
		}

		public function trigger():string {

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

			$descriptor = $this->descriptor;

			$container = $descriptor->getContainer();

			$this->router = new RouteManager($descriptor, $container, $this->requestPath, $this->requestMethod);

			$this->bindDefaultObjects();

			$container->setServiceProviders($descriptor->getServiceProviders())

			->setLibraryConfigurations($descriptor->getLibraryConfigurations());

			$this->responseManager = $container->getClass(ResponseManager::class);

			return $this;
		}

		private function bindDefaultObjects():void {
			
			$this->descriptor->getContainer()->whenTypeAny()

			->needsAny([

				ModuleDescriptor::class => $this->descriptor, // all requests for the parent should respond with the active module

				RouteManager::class => $this->router
			]);
		}

		public function didFindRoute():bool {
			
			return $this->foundRoute;
		}
	}
?>