<?php

	namespace Tilwa\Routing;

	use Tilwa\App\Bootstrap;

	use Tilwa\Contracts\Orm;

	class RouteManager {

		private $app;

		private $activeRoute;

		private $payload;

		private $handlerParameters;

		private $databaseModel;

		function __construct(Bootstrap $app ) {

			$this->app = $app;
			
			$this->databaseModel = $this->app->getClass(Orm::class);
		}

		/**
		 * @param {reqPath}: does not support query urls
		 *
		 **/
		public function findRoute (string $reqPath, int $reqMethod ) {

			if (preg_match('/^\/?$/', $reqPath))

				return $this->findRoute('index', $reqMethod);

			$regx = '/\{(\w+)?(\?)\}/';

			$parameterPair = [];

			$allRoutes = $this->app->routeCatalog->registeredRoutes();

			// search register for route matching this pattern
			$target = @array_filter($allRoutes, function ($route) use ( $regx, $reqPath, $reqMethod, &$parameterPair) {

				// convert /jui/{fsdf}/weeer to /jui/\w+/weeer
				// /jui/{fsdf?}/weeer to /jui/(\/\w+)?weeer
				$tempPat = preg_replace_callback($regx, function ($m) {

					$routeToken = $m[1];

					$wordPlaceholder = "(?P<$routeToken>\/\w+)"; // return assoc matches
					if (isset($m[2]))

						$wordPlaceholder .= '?';
var_dump($routeToken, $wordPlaceholder);
					return $wordPlaceholder;
				}, preg_quote($route->pattern) );

				$numMatches = preg_match_all("/^\/?$tempPat$/", $reqPath, $parameterPair); // permit non-prefixed registered patterns to match requests for slash prefixed
					// var_dump($parameterPair, $numMatches, $tempPat);
				return $numMatches !== 0 && $route->method === $reqMethod;
			});
//var_dump($target); die();
			$target = current($target);

			if ($target !== false) {

				$target->placeholderMap = $parameterPair;

				$target->setPath($reqPath);
			}

			return $target;
		}

		public function setPrevious(Route $route ):static {

			$_SESSION['prev_route'] = $route;

			return $this;
		}

		public function getPrevious ():Route {

			return $_SESSION['prev_route'];
		}

		public function getActiveRoute ():Route {

			return $this->activeRoute;
		}

		public function setActiveRoute (Route $route):static {

			$this->activeRoute = $route;

			return $this;
		}

		public function savePayload():static {
			
			$payloadAnchor = 'tilwa_request';

			$this->payload = array_filter($_GET + $_POST, function ( $key) use ($payloadAnchor) {

				return $key !== $payloadAnchor;
			}, ARRAY_FILTER_USE_KEY);

			unset($_GET[$payloadAnchor], $_POST[$payloadAnchor]);

			return $this;
		}

		/**
		* @return previous Route
		*/
		public function mergeWithPrevious(BaseRequest $request):Route {
			
			$route = $this->getPrevious();

			$route->getRequest()

			->setValidationErrors( $request->validationErrors() );

			return $route;
		}

		public function prepareArguments():array {

			$route = $this->activeRoute;

			$this->handlerParameters = $this->app->getMethodParameters($route->getController(), $route->handler); // we might need to pull this property out of the route class

			$request = $this->updateRequestPayload();

			$route->setRequest ($request);

			$this->hydrateDatabaseModels()

			->resolveInitiator($request);

			return $this->handlerParameters;
		}

		// set a request for the current route. update the parameter list if client injected request class so they can access the payload
		private function updateRequestPayload():BaseRequest {

			$requestIndex = $request = null;
			
			foreach ($this->handlerParameters as $idx => $arg) {
				
				if ($arg instanceof BaseRequest) {
				
					$requestIndex = $idx;

					$request = $arg;
				}
			}
			if (is_null($request))

				$request = $this->app->getClass(BaseRequest::class);

			$request->setPayload($this->payload); // TODO: consider adding a payload parameter on the BaseRequest constructor. then when-wantsArgs-give for it. it'll save us these 3 lines

			if (!is_null($requestIndex))

				$this->handlerParameters[$requestIndex] = $request;

			return $request;
		}

		public function hydrateDatabaseModels():static {

			$pathPlaceholders = array_values($this->activeRoute->placeholderMap);
			
			foreach ($this->handlerParameters as $idx => $arg) { // there probably is a better implementation than the assumption that url placeholders directly correspond to handler arguments
				
				if ($arg instanceof $this->databaseModel)
				
					$this->handlerParameters[ $idx] = $this->databaseModel->find($pathPlaceholders[$idx]);
			}
			return $this;
		}

		private function resolveInitiator(BaseRequest $request) {
			
			$request->setInitiator($this->model->auth());
		}
	}
?>