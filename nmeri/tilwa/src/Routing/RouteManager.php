<?php

	namespace Tilwa\Routing;

	use Tilwa\App\Bootstrap;

	use Tilwa\Contracts\Orm;

	class RouteManager {

		private $app;

		private $activeRoute;

		private $payload;

		private $handlerParameters;

		private $databaseAdapter;

		private $requestIndexInParameters;

		private $modelIndexesInParameters;

		function __construct(Bootstrap $app ) {

			$this->app = $app;

			$this->databaseAdapter = $app->getClass(Orm::class);
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

				$target->placeholderMap = $parameterPair; // this should update the current payload list. if it isn't called after `setPayload`, clobber it in when that guy is called

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

			$this->handlerParameters = $this->app->getMethodParameters($route->getController(), $route->handler);

			$this->warmParameters();

			if (!is_null($this->requestIndexInParameters))

				$this->updateRequestPayload();

			if (!empty($this->modelIndexesInParameters))

				$this->hydrateModels();

			return $this->handlerParameters;
		}

		private function warmParameters():void {
			
			foreach ($this->handlerParameters as $idx => $arg) {
				
				if ($arg instanceof BaseRequest)
				
					$this->requestIndexInParameters = $idx;

				elseif ($arg instanceof $this->databaseAdapter)

					$this->modelIndexesInParameters[] = $idx;
			}
		}

		private function updateRequestPayload():void {

			$request = $this->handlerParameters[$this->requestIndexInParameters]->setPayload($this->payload);

			$this->initializeUser($request);

			$this->activeRoute->setRequest ($request);
		}

		/*
		* @description: assumes ordering of the arguments on the action handler matches the one on url pattern

			handler (BaseRequest, Model1, Random, Model2)
			path/2/action/3 = [2,3]
		*/
		private function hydrateModels():void {

			$pathPlaceholders = array_values($this->activeRoute->placeholderMap); // 
			
			foreach ($this->modelIndexesInParameters as $index => $modelIndex)

				$this->handlerParameters[ $modelIndex] = $this->databaseAdapter->findOne($pathPlaceholders[$index]);
		}

		private function initializeUser(BaseRequest $request) {
			
			$request->setUserResolver($this->databaseAdapter);
		}
	}
?>