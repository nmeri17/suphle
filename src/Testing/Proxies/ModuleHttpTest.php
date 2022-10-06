<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Hydration\Container;

	use Suphle\Modules\ModuleToRoute;

	use Suphle\Middleware\MiddlewareRegistry;

	use Suphle\Testing\{Condiments\DirectHttpTest, Proxies\Extensions\TestResponseBridge};

	use Suphle\Exception\Explosives\NotFoundException;

	trait ModuleHttpTest {

		use DirectHttpTest, ExaminesHttpResponse;

		private $JSON_HEADER_VALUE = "application/json",

		$CONTENT_TYPE_KEY = "Content-Type", $HAS_ROUTED = ModuleToRoute::class,

		$staticHeaders = [], $mockMiddlewareRegistry;

		public function withHeaders(array $headers):self {

			$this->staticHeaders = array_merge($this->staticHeaders, $headers);

			return $this;
		}

		public function withToken(string $token, string $type = "Bearer"):self {

			$this->staticHeaders["Authorization"] = $type . " " . $token;

			return $this;
		}

		protected function activeModuleContainer ():Container {

			$defaultContainer = $this->firstModuleContainer();

			if (is_null(

				$defaultContainer->decorateProvidedConcrete($this->HAS_ROUTED)
			))

				return $defaultContainer; // don't contaminate original until actual routing occurs
var_dump(48, spl_object_hash($defaultContainer)); // this guy != prelim
			$activeModule = $defaultContainer->getClass($this->HAS_ROUTED)

			->getActiveModule(); // this is where the problem is. it always returns the old one
			var_dump(52, is_null($activeModule));// is prelim free of mtr? this should be true until routing occurs

			if (!is_null($activeModule)) // if [gatewayResponse] has not been called

				return $activeModule->getContainer();

			return $defaultContainer;
		}

		public function from (string $url):self {

			$this->setHttpParams($url);

			$initializer = $this->firstModuleContainer()

			->getClass($this->HAS_ROUTED)->findContext($this->getModules());

			if (!$initializer)

				throw new NotFoundException;

			$initializer->getRouter()

			->setPreviousRenderer($initializer->handlingRenderer());

			return $this;
		}

		/**
		 * Assumes there's some behavior this middleware may have that we aren't comfortable triggering
		 * 
		 * @param {middleware} Middleware[]
		*/
		public function withoutMiddleware(array $middlewares = []):self {

			$this->setMiddlewareRegistry();

			if (empty($middlewares))

				$this->mockMiddlewareRegistry->disableAll();

			else $this->mockMiddlewareRegistry->disable($middlewares);

			return $this;
		}

		/**
		 * Useful when we want to see the implication of using a particular middleware, in test
		 * 
		 * @param {middleware} Middleware[]
		*/
		public function withMiddleware(array $middlewares):self {

			$this->setMiddlewareRegistry();

			$this->mockMiddlewareRegistry->addToActiveStack($middlewares);

			return $this;
		}

		private function setMiddlewareRegistry ():void {

			if (is_null($this->mockMiddlewareRegistry)) {

				$this->mockMiddlewareRegistry = new MiddlewareManipulator;

				$this->massProvide([

					MiddlewareRegistry::class => $this->mockMiddlewareRegistry
				]);
			}
		}

		protected function assertUsedMiddleware (array $middlewares) {

			$matches = $this->getMatchingMiddleware($middlewares);

			$unused = array_diff($middlewares, $matches);

			$this->assertEmpty($unused,

				"Failed to assert that middlewares ".

				json_encode($unused, JSON_PRETTY_PRINT). " were used"
			);
		}

		protected function assertDidntUseMiddleware (array $middlewares) {

			$intersectingUsed = $this->getMatchingMiddleware($middlewares);

			$this->assertEmpty($intersectingUsed,

				"Did not expect to use middlewares " .

				json_encode($intersectingUsed, JSON_PRETTY_PRINT)
			);
		}

		private function getMatchingMiddleware (array $middlewares):array {

			$registry = $this->activeModuleContainer()->getClass(MiddlewareRegistry::class);

			$matches = [];

			foreach ($registry->getActiveStack() as $collection) {

				$intersect = array_intersect($collection->getList(), $middlewares);

				$matches = array_merge($matches, $intersect);
			}

			return $matches;
		}

		public function get(string $url, array $headers = []):TestResponseBridge {

			return $this->gatewayResponse($url, __FUNCTION__, null, $headers);
		}

		public function getJson(string $url, array $headers = []):TestResponseBridge {

			return $this->json( "get", $url, null, $headers);
		}

		private function gatewayResponse (
			string $requestPath, string $httpMethod, ?array $payload,

			array $headers, array $files = []
		):TestResponseBridge {

			$entrance = $this->entrance;

			$this->setHttpParams($requestPath, $httpMethod, $payload, $headers);

			$this->provideFileObjects($files, $httpMethod);

			$entrance->diffuseSetResponse(false); // Writing anything to the real headers is redundant in test environment

			$renderer = $entrance->underlyingRenderer();

			return $this->makeExaminable($renderer);
		}

		public function post (
			string $url, array $payload = [], array $headers = [],

			array $files = []
		):TestResponseBridge {

			return $this->gatewayResponse(

				$url, __FUNCTION__, $payload, $headers, $files
			);
		}

		public function postJson (
			string $url, array $payload = [], array $headers = [],

			array $files = []
		):TestResponseBridge {

			return $this->json("post", $url, $payload, $headers, $files);
		}

		public function put (
			string $url, array $payload = [], array $headers = [],

			array $files = []
		):TestResponseBridge {

			return $this->gatewayResponse(

				$url, __FUNCTION__, $payload, $headers, $files
			);
		}

		public function putJson (

			string $url, array $payload = [], array $headers = [],

			array $files = []
		):TestResponseBridge {

			return $this->json("put", $url, $payload, $headers, $files);
		}

		public function delete(string $url, array $payload = [], array $headers = []):TestResponseBridge {

			return $this->gatewayResponse($url, __FUNCTION__, $payload, $headers);
		}

		public function deleteJson(string $url, array $payload = [], array $headers = []):TestResponseBridge {

			return $this->json("delete", $url, $payload, $headers);
		}

		public function json(
			string $httpMethod, string $url, array $payload = [],

			array $headers = [], array $files = []
		):TestResponseBridge {

			$converted = json_encode($payload);

			$newHeaders = array_merge([
				"Content-Length" => mb_strlen($converted, "8bit"),

				$this->CONTENT_TYPE_KEY => $this->JSON_HEADER_VALUE,

				"Accept" => $this->JSON_HEADER_VALUE
			], $headers);

			return $this->gatewayResponse(

				$url, $httpMethod, $payload, $newHeaders, $files
			);
		}
	}
?>