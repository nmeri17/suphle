<?php

	namespace Controllers;

	use Tilwa\Route\Route;
	
	class FrontController {

		private $getCtrl;

		private $value;

		private $handler;

		public $response;

		private $app;

		function __construct() {

			$reqUrl = $_GET['tilwa_request'];

			$app = new Bootstrap( $reqUrl );


			if ( $target = $app->router->findRoute($reqUrl, $_SERVER['REQUEST_METHOD'] )) $this->validRequest( $app, $target );

			else {

				$target = $app->router->findRoute( '404', 'get' );

				$this->response = $app->getClass(GetController::class)->pairVarToFields( '404', $target );
			}
		}

		private function validRequest ( $app, $target ) {

			if ($middlewares = $target->getMiddlewares())

				$target = $this->runMiddleware( $middlewares, $app, $target); // THIS IS UNTESTED

			// if anything other than a Route object is returned, we will assume request couldn't make it past middleware
			if ( !is_a($target, Route::class) ) $this->response = $target;

			else {

				$payload = array_filter($_GET + $_POST, function ( $key) {

					return $key !== 'tilwa_request';
				}, ARRAY_FILTER_USE_KEY);

				$this->response = $app->getClass(GetController::class)

				->pairVarToFields( @end(explode('/', $app->requestSlug)), $target, $payload );
			}
		}

		// middleware delimited by commas. Middleware params delimited by colons
		private function runMiddleware ( array $middlewares, array $app, Route $route) {

			foreach ($middlewares as $mw ) {

				[$clsName, $args] = explode(',', $mw);

				$fullyQualified = $app->middlewareDirectory . '\\' . $clsName;
// var_dump($fullyQualified); die();
				$instance = new $fullyQualified ( $app, $route ); # assume all your middleware are namespaced

				$route = $instance->handle(function ( $data ) {

					return $data; // pass args to successive middleware
				}, ...explode(':', $args));

				if ( !is_a($route, Route::class) ) return $route; // terminate
			}
		}
	}

	chdir('../'); // changing to root so scripts at other locations can use that autoloader

	require 'autoload.php';

	$entrance = new FrontController;

	echo $entrance->response;

?>