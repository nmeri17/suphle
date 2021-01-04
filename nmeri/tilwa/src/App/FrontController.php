<?php

	namespace App;

	use Tilwa\Routing\Route;

	use Tilwa\Http\Response\ResponseManager;
	
	class FrontController {

		public $responseManager;

		private $module;

		public $postResEventList;

		private $router;

		function __construct(Bootstrap $module) {

			$this->module = $module;

			$this->initializeProps();

			$this->assignRequestRoute( $_GET['tilwa_request'] );
		}

		public function initializeProps() {

			$this->postResEventList = [];

			$this->router = new RouteManager($this->module); // this guy should now be the new route repository

			$this->responseManager = new ResponseManager($this->module, $this->router);
		}

		private function assignRequestRoute( $requestUrl):Route {
			
			$httpMethod = strtolower(

				$_POST["_method"] ?? $_SERVER['REQUEST_METHOD']
			);

			if ($target = $this->router->findRoute( $requestUrl, $httpMethod ) ) {

				$this->router->setActiveRoute($target)

				->savePayload();

				if ($middlewares = $target->getMiddlewares())

					$this->runMiddleware( $middlewares );
			}

			return $this;
		}

		// middleware delimited by commas. Middleware params delimited by colons
		private function runMiddleware ( array $middlewares ):bool {

			foreach ($middlewares as $mw ) {

				@[$clsName, $args] = explode(',', $mw);

				$fullyQualified = $this->module->middlewareDirectory . '\\' . $clsName;

				$instance = new $fullyQualified($this->module); # assumes all your middleware are namespaced

				if (is_callable($instance->postSourceBehavior)) {

					$this->postResEventList[] = $instance->postSourceBehavior;

					$passed = true;
				}

				else $passed = $instance->handle( explode(':', $args) );

				if ( !$passed ) return false; // terminate
			}

			return true;
		}
	}
?>