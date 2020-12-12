<?php

	namespace Tilwa\Http\Response;

	use Phpfastcache\CacheManager;

	use Phpfastcache\Config\ConfigurationOption;

	use Tilwa\App\Bootstrap;

	use Tilwa\Routing\Route;

	class ResponseManager {

		function __construct (Bootstrap $app ) {

			$this->app = $app;
		}
		
		public function getResponse () {
			
			return $this->getValidRoute()

			->executeHandler()->renderResponse();
		}

		private function getValidRoute ():Route {

			$router = $this->app->router;

			$route = $router->getActiveRoute();

			$request = $route->getRequest();

			if ($request->validated())

				$router->pushPrevRequest($request);

			else $route = $router->revertRoute($request);

			return $route;
		}

		public function cacheManager() {

			CacheManager::setDefaultConfig(new ConfigurationOption([

				"path" =>  $this->app->rootPath ."/req-cache"
			]));

			return CacheManager::getInstance();
		}
	}
?>