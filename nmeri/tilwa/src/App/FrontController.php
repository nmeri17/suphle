<?php

	namespace App;

	use Tilwa\Http\Response\ResponseManager;
	
	class FrontController {

		private $responseManager;

		private $router;

		public $foundRoute;

		function __construct(Bootstrap $module) {

			$this->router = new RouteManager($module); // this guy should now be the new route repository

			$this->responseManager = new ResponseManager($module, $this->router);
		}

		public function assignRoute():self {
			
			$httpMethod = strtolower(

				$_POST["_method"] ?? $_SERVER['REQUEST_METHOD']
			);

			if ($target = $this->router->findRoute( $_GET['tilwa_request'], $httpMethod ) ) {

				$this->router->setActiveRoute($target)->savePayload();

				$this->foundRoute = true;
			}
			return $this;
		}

		public function trigger():string {

			return $this->responseManager->getResponse();
		}
	}
?>