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

			return $this->responseManager->getResponse();
		}
	}
?>