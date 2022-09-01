<?php
	namespace Suphle\Bridge\Laravel\Routing;

	use Suphle\Contracts\{Config\Laravel as LaravelConfig, Bridge\LaravelContainer, Presentation\BaseRenderer, Routing\ExternalRouter};

	use Suphle\Response\Format\ExternallyEvaluatedRenderer;

	use Illuminate\Routing\{Router, Route};

	use Illuminate\Http\Request;

	use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

	class ModuleRouteMatcher implements ExternalRouter {

		private $config, $laravelContainer, $router, $request;

		public function __construct (LaravelConfig $config, LaravelContainer $laravelContainer) {

			$this->config = $config;

			$this->laravelContainer = $laravelContainer;
		}

		public function canHandleRequest ():bool {

			if (!$this->config->registersRoutes()) return false;

			$this->router = $this->laravelContainer->make(Router::class);

			$this->request = $this->laravelContainer->make(Request::class);

			try {
				
				return $this->router->getRoutes()->match($this->request) instanceof Route;
			} catch (NotFoundHttpException $e) {
			
				return false;	
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