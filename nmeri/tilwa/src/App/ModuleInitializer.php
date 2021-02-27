<?php

	namespace Tilwa\App;

	use Tilwa\Http\Response\ResponseManager;

	use Tilwa\Routing\RouteManager;
	
	class ModuleInitializer {

		private $router;

		public $foundRoute;

		private $module;

		function __construct(ParentModule $module, string $requestQuery) {

			$this->module = $module;

			$this->router = new RouteManager($module, $requestQuery, $this->getHttpMethod());

			$module->entityBindings($this->router); // idk how reasonable it is to insert this from here considering how many defaults we could potentially wanna pass. But there's no better candidate to delegate initialization of the router to. And this is our last contact with the module before route finding commences
		}

		public function assignRoute():self {
			
			if ($target = $this->router->findRenderer() ) { // what are the chances of the guys inside here or the container looking for a route manager?

				$this->router->setActiveRenderer($target)->savePayload();

				$this->foundRoute = true;
			}
			return $this;
		}

		public function trigger():string {

			return (new ResponseManager($this->module->container, $this->router))->getResponse();
		}

		private function getHttpMethod ():string {

			return strtolower(

				$_POST["_method"] ?? $_SERVER['REQUEST_METHOD']
			);
		}

		public function getModule():ParentModule {
			
			return $this->module;
		}
	}
?>