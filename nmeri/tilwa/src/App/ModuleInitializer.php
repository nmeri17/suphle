<?php

	namespace Tilwa\App;

	use Tilwa\Http\Response\ResponseManager;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Contracts\Config\ModuleFiles;
	
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
			// else if routeConfig->hasLaravelRoutes() check with that guy's router
			
			return $this;
		}

		public function triggerRequest():string {

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

			$container->setServiceProviders($descriptor->getServiceProviders());

			$this->bindDefaultObjects();

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

		private function lazyContainerBindings(ModuleFiles $fileConfig):void {

			$container->whenType(Application::class)

			->needsArguments([
				
				"basePath" => $fileConfig->activeModulePath()
			]);
		}

		public function whenActive ():self {

			$descriptor = $this->descriptor;

			$container = $descriptor->getContainer();

			$container->setLibraryConfigurations($descriptor->getLibraryConfigurations());

			$internalBindings = $container->getMethodParameters("lazyContainerBindings", $this);

			$customBindings = $container->getMethodParameters("entityBindings", $descriptor);
			
			call_user_func_array([$this, "lazyContainerBindings"], $internalBindings);

			call_user_func_array([$descriptor, "entityBindings"], $customBindings);

			return $this;
		}
	}
?>