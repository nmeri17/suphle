<?php

	namespace App;

	use Tilwa\Http\Response\ResponseManager;
	
	class FrontController {

		private $responseManager;

		private $router;

		public $foundRoute;

		function __construct(ParentModule $module) {

			$this->setRouter($module->activate());

			$this->responseManager = new ResponseManager($module->container, $this->router);
		}

		public function assignRoute():self {
			
			if ($target = $this->router->findRenderer() ) {

				$this->router->setActiveRenderer($target)->savePayload();

				$this->foundRoute = true;
			}
			return $this;
		}

		public function trigger():string {

			return $this->responseManager->getResponse();
		}

		private function setRouter ($module) {

			$this->router = new RouteManager($module, $_GET['tilwa_request'], $this->getHttpMethod());

			$module->container->whenTypeAny()->needsAny([

				RouteManager::class => $this->router
			]);
		}

		private function getHttpMethod ():string {

			return strtolower(

				$_POST["_method"] ?? $_SERVER['REQUEST_METHOD']
			);
		}
	}
?>