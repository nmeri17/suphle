<?php
	namespace Tilwa\Bridge\Laravel\Routing;

	use Tilwa\Contracts\{Config\Laravel as LaravelConfig, Bridge\LaravelContainer};

	use Tilwa\Hydration\LaravelProviderManager;

	use Illuminate\Routing\Router;

	use Illuminate\Http\{Request, Response};

	class ModuleRouteMatcher {

		private $config, $laravelContainer, $router, $request,

		$providerBooter;

		public function __construct (LaravelConfig $config, LaravelContainer $laravelContainer, LaravelProviderManager $providerBooter) {

			$this->config = $config;

			$this->laravelContainer = $laravelContainer;

			$this->providerBooter = $providerBooter;
		}

		public function canHandleRequest ():bool {

			$routeProviders = $this->config->registersRoutes();

			if (!empty($routeProviders)) {

				$this->activateProviders($routeProviders);

				$this->router = $this->laravelContainer->make(Router::class);

				$this->request = $this->laravelContainer->make(Request::class);

				return $this->router->getRoutes()->match($this->request);
			}

			return false;
		}

		private function activateProviders (array $providers):void {

			$booter = $this->providerBooter;

			foreach ($providers as $providerName) {

				$booter->setActiveProvider($providerName);
				
				$concrete = $booter->getActiveProvider();

				// we're using this instead of actually providing the service, to slightly speed up boot process since we don't know if this will be the eventual handler
				$booter->createSandbox(function () use ($concrete) {

					$concrete->register(); // idk how necessary this is since routes are registered in the boot method
				
					$concrete->boot();
				});
			}
		}
		
		public function getResponse ():Response {

			return $this->router->dispatch($this->request);
		}
	}
?>