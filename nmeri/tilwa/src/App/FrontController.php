<?php

	namespace App;

	use Tilwa\Http\Response\ResponseManager;
	
	class FrontController {

		private $responseManager;

		private $router;

		public $foundRoute;

		function __construct(Bootstrap $module) {

			$this->setRouter($module);

			$this->responseManager = new ResponseManager($module, $this->router);
		}

		public function assignRoute():self {
			
			if ($target = $this->router->findRoute() ) {

				$this->router->setActiveRoute($target)->savePayload();

				$this->foundRoute = true;
			}
			return $this;
		}

		public function trigger():string {

			return $this->responseManager->getResponse();
		}

		private function setRouter ($module) {

			$this->router = new RouteManager($module, $_GET['tilwa_request'], $this->getHttpMethod()); // this guy should now be the new route repository

			$module->whenTypeAny()->needsAny(RouteManager::class)

			->give($this->router);
		}

		private function getHttpMethod ():string {

			return strtolower(

				$_POST["_method"] ?? $_SERVER['REQUEST_METHOD']
			);
		}
	}
?>