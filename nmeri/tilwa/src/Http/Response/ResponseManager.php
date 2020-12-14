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

			if (!$request->isValidated())

				$route = $router->revertRoute($request);

			else /*if ($this->app->getClass(Tilwa\Contracts\Auth)->name !== "browser")*/ $router->pushPrevRequest($request); // uncomment when that is implemented

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