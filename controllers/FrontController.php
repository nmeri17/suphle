<?php

	namespace Controllers;

	use Tilwa\Route\Route;
	
	class FrontController {

		public $response;

		private $app;

		public $postResEventList;

		/**
		* @property array*/
		private $reqPayload;

		function __construct() {

			$this->postResEventList = [];

			$this->setPayload();

			$this->app = new Bootstrap;

			$this->response = $this->app->setSingleton( // once a valid route is found, bind the app instance in its container, before diving in to derive the proper response for it
				Bootstrap::class,

				$this->prepareRequest( $_GET['tilwa_request'] )
			)

			->getClass(GetController::class)

			->pairVarToFields( $this->reqPayload );
		}

		// pimp route vars, designate handler etc
		private function prepareRequest( $reqUrl):Bootstrap {
			
			$userMethod = constant(Route::class . '::'. $_SERVER['REQUEST_METHOD']);

			$app = $this->app;

			if ( $target = $app->router

				->findRoute( $reqUrl, $userMethod )
			) {

				$app->setActiveRoute($target->setPath($reqUrl));

				if ($middlewares = $app->getActiveRoute()->getMiddlewares())

					$this->runMiddleware( $middlewares );
			}

			else {

				http_response_code(404);

				$target = $app->router->findRoute( '404', Route::GET );

				$app->setActiveRoute($target);

				$this->reqPayload['error_url'] = $reqUrl; // use parameterized url for this instead
			}

			return $app;
		}

		// middleware delimited by commas. Middleware params delimited by colons
		private function runMiddleware ( array $middlewares ):bool {

			foreach ($middlewares as $mw ) {

				@[$clsName, $args] = explode(',', $mw);

				$fullyQualified = $this->app->middlewareDirectory . '\\' . $clsName;

				$instance = new $fullyQualified($this->app); # assumes all your middleware are namespaced

				$passed = $instance->handle( explode(':', $args));

				if (is_callable($instance->postSourceBehavior)) // doubt this will ever be used

					$this->postResEventList[] = $instance->postSourceBehavior;

				if ( !$passed ) return false; // terminate
			}

			return true;
		}

		private function setPayload() { // this should go to the route instead
			
			$this->reqPayload = array_filter($_GET + $_POST, function ( $key) {

				return $key !== 'tilwa_request';
			}, ARRAY_FILTER_USE_KEY);

			return $this;
		}
	}

	require '../autoload.php';

	$entrance = new FrontController;

	$preRespo = $entrance->response;

	foreach ($entrance->postResEventList as $handler)

		$preRespo = $handler($preRespo);

	echo $preRespo;

?>