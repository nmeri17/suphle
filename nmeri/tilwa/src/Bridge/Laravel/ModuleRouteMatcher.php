<?php

	namespace Tilwa\Bridge\Laravel;

	use Tilwa\Contracts\Config\Laravel as LaravelConfig;

	use Illuminate\Routing\Router;

	use Illuminate\Http\{Request, Response};

	class ModuleRouteMatcher {

		private $config, $laravelContainer, $router;

		public function __construct (LaravelConfig $config, LaravelApp $laravelContainer) {

			$this->config = $config;

			$this->laravelContainer = $laravelContainer;
		}

		private function hasLaravelRoutes ():bool {
			
			return $this->config->hasRoutes();
		}

		private function getRouter():Router {

			if (is_null($this->router))

				$this->router = $this->laravelContainer->make(Router::class);

			return $this->router;
		}

		public function canHandleRequest ():bool {

			if ($this->hasLaravelRoutes()) {

				$request = $this->laravelContainer->make(Request::class);

				return $this->getRouter()->getRoutes()->match($request);
			}

			return false;
		}
		
		public function getResponse ():Response {

			$request = $this->laravelContainer->make(Request::class);

			return $this->getRouter()->dispatch($request);
		}
	}
?>