<?php
	namespace Tilwa\Bridge\Laravel\Routing;

	use Tilwa\Contracts\{Config\Laravel as LaravelConfig, Bridge\LaravelContainer, Presentation\BaseRenderer};

	use Tilwa\Response\Format\ExternallyEvaluatedRenderer;

	use Tilwa\Bridge\Laravel\Package\LaravelProviderManager;

	use Illuminate\Routing\{Router, Route};

	use Illuminate\Http\Request;

	use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

			if (empty($routeProviders)) return false;

			$this->activateProviders($routeProviders);

			$this->router = $this->laravelContainer->make(Router::class);

			$this->request = $this->laravelContainer->make(Request::class);

			try {
				
				return $this->router->getRoutes()->match($this->request) instanceof Route;
			} catch (NotFoundHttpException $e) {
			
				return false;	
			}
		}

		private function activateProviders (array $providers):void {

			$booter = $this->providerBooter;

			foreach ($providers as $providerName) {

				$booter->setActiveProvider($providerName);
				
				$concrete = $booter->getActiveProvider();

				$concrete->register(); // idk how necessary this is since routes are registered in the boot method
			
				$concrete->boot();
			}
		}
		
		public function convertToRenderer ():BaseRenderer {

			$fullRequest = $this->router->dispatch($this->request);

			$renderer = (new ExternallyEvaluatedRenderer)

			->setRawResponse($fullRequest->getContent());

			$renderer->setHeaders(
				$fullRequest->getStatusCode(),

				$fullRequest->headers->all()
			);

			return $renderer;
		}
	}
?>