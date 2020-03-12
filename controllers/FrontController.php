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

			$app = new Bootstrap;

			$this->response = $app->setSingleton( // once a valid route is found, bind the app instance in its container, before diving in to derive the proper response for it
				Bootstrap::class, $this->prepareRequest(
					
					$app, $_GET['tilwa_request']
				)
			)

			->getClass(GetController::class)

			->pairVarToFields( $this->reqPayload );
		}

		// pimp route vars, designate handler etc
		private function prepareRequest($app, $reqUrl):Bootstrap {
			
			$userMethod = constant(Route::class . '::'. $_SERVER['REQUEST_METHOD']);

			if ( $target = $app->router->findRoute($reqUrl, $userMethod )) {

				$app->setActiveRoute($target->setPath($reqUrl));

				if ($middlewares = $app->getActiveRoute()->getMiddlewares())

					$this->runMiddleware( $middlewares, $app);
			}

			else {

				$target = $app->router->findRoute( '404', Route::GET );

				$app->setActiveRoute($target);
			}

			return $app;
		}

		// middleware delimited by commas. Middleware params delimited by colons
		private function runMiddleware ( array $middlewares, array $app):bool {

			foreach ($middlewares as $mw ) {

				[$clsName, $args] = explode(',', $mw);

				$fullyQualified = $app->middlewareDirectory . '\\' . $clsName;

				$instance = new $fullyQualified($app); # assumes all your middleware are namespaced

				$passed = $instance->handle( explode(':', $args));

				if (is_callable($instance->postSourceBehavior))

					$this->postResEventList[] = $instance->postSourceBehavior;

				if ( !$passed ) return false; // terminate
			}

			return true;
		}

		private function setPayload():void {
			
			$this->reqPayload = array_filter($_GET + $_POST, function ( $key) {

				return $key !== 'tilwa_request';
			}, ARRAY_FILTER_USE_KEY);
		}
	}

	require '../autoload.php';

	$entrance = new FrontController;

	$preRespo = $entrance->response;

	foreach ($entrance->postResEventList as $handler)

		$preRespo = $handler($preRespo);

	echo $preRespo;

?>