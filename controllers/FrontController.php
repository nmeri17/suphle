<?php

	namespace Controllers;

	use Nmeri\Tilwa\Route\Route;

	
	class FrontController {

		private $getCtrl;

		private $value;

		private $handler;

		public $response;

		private $app;

		function __construct() {

			$reqUrl = $_GET['url'];

			$app = new Bootstrap( $reqUrl );


			if ( $target = $app->router->findRoute($reqUrl )) $this->validRequest( $app, $target );

			else {

				$target = $app->router->findRoute( '404' );

				$this->response = $app->getClass(GetController::class)->pairVarToFields( '404', $target );
			}
		}

		private function validRequest ( $app, $target ) {

			if ($middlewares = $target->getMiddlewares())

				$target = $this->runMiddleware( $middlewares, $app, $target) );

			// if anything other than a Route object is returned, we will assume request couldn't make it past middleware
			if ( !is_a($target, Route::class) ) $this->response = $target;

			else {

				$this->response = $app->getClass(GetController::class)

				->pairVarToFields( end(@explode('/', $app->requestName)), $target );
			}
		}

		// middleware delimited by commas. Middleware params delimited by colons
		private function runMiddleware ( array $middlewares, array $app, Route $route) {

			foreach ($middlewares as $mw ) {

				[$clsName, $args] = explode(',', $mw);

				$instance = new { $app->middlewareDirectory . '\\' . $clsName} ( $app, $route ); # assume all your middleware are namespaced

				$route = $instance->handle(function ( $data ) {

					return $data; // pass args to successive middleware
				}, ...explode(':', $args));

				if ( !is_a($route, Route::class) ) return $route; // terminate
			}
		}
	}

	$entrance = new FrontController;

	echo $entrance->response;

?>