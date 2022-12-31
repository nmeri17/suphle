<?php
	namespace Suphle\Bridge\Laravel\Routing;

	use Suphle\Contracts\{Config\Laravel as LaravelConfig, Bridge\LaravelContainer, Presentation\BaseRenderer, Routing\ExternalRouter};

	use Suphle\Response\Format\ExternallyEvaluatedRenderer;

	use Illuminate\Routing\{Router, Route};

	use Illuminate\Http\Request;

	use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

	class ModuleRouteMatcher implements ExternalRouter {

		protected Router $router;

  		protected Request $request;

		public function __construct(
			protected readonly LaravelConfig $config,

			protected readonly LaravelContainer $laravelContainer
		) {

			//
		}

		public function canHandleRequest ():bool {

			if (!$this->config->registersRoutes()) return false;

			$this->router = $this->laravelContainer->make(Router::class);

			$this->request = $this->laravelContainer->make(Request::class);

			try {
				
				return $this->router->getRoutes()->match($this->request) instanceof Route;
			} catch (NotFoundHttpException) {
			
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