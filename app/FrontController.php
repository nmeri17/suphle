<?php

	namespace App;

	use Tilwa\Routing\Route;

	use Tilwa\Http\Response\ResponseManager;
	
	class FrontController {

		public $responseManager;

		private $app;

		public $postResEventList;

		function __construct() {

			$this->initializeProps();

			$this->assignRequestRoute( $_GET['tilwa_request'] );

			$this->app->whenType("*")->needs( Bootstrap::class)

			->give( $this->app);
		}

		public function initializeProps() {

			$this->postResEventList = [];

			$this->app = new Bootstrap;

			$this->responseManager = new ResponseManager($this->app);
		}

		private function assignRequestRoute( $requestUrl):Route {
			
			$userMethod = constant(Route::class . '::'. $_SERVER['REQUEST_METHOD']); // CORRECT THIS IMPL

			$router = $this->app->router;

			if ($target = $router->findRoute( $requestUrl, $userMethod ) ) {

				$router->setActiveRoute($target)->setPayload();

				if ($middlewares = $target->getMiddlewares())

					$this->runMiddleware( $middlewares );
			}

			else {

				http_response_code(404);

				$target = $router->findRoute( '404', Route::GET );

				$router->setActiveRoute($target)->setPayload([

					'error_url' => $requestUrl
				]);
			}

			return $this;
		}

		// middleware delimited by commas. Middleware params delimited by colons
		private function runMiddleware ( array $middlewares ):bool {

			foreach ($middlewares as $mw ) {

				@[$clsName, $args] = explode(',', $mw);

				$fullyQualified = $this->app->middlewareDirectory . '\\' . $clsName;

				$instance = new $fullyQualified($this->app); # assumes all your middleware are namespaced

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

	require '../autoload.php';

	$entrance = new FrontController;

	$preRespo = $entrance->responseManager->getResponse();

	foreach ($entrance->postResEventList as $handler)

		$preRespo = $handler($preRespo);

	echo $preRespo;

?>