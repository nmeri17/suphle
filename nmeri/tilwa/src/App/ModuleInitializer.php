<?php

	namespace Tilwa\App;

	use Tilwa\Http\Response\ResponseManager;

	use Tilwa\Routing\RouteManager;
	
	class ModuleInitializer {

		private $router;

		public $foundRoute;

		private $module;

		private $responseManager;

		function __construct(ParentModule $module, ResponseManager $responseManager, RouteManager $router) {

			$this->module = $module;

			$this->router = $router;
			
			$this->responseManager = $responseManager;
		}

		public function assignRoute():self {
			
			if ($target = $this->router->findRenderer() ) { // what are the chances of the guys inside here looking for a route manager?

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
	}
?>