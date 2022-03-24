<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Testing\Condiments\DirectHttpTest;

	use Tilwa\Hydration\Container;

	trait ModuleHttpTest {

		use DirectHttpTest, ExaminesHttpResponse;

		private $JSON_HEADER_VALUE = "application/json";

		private $CONTENT_TYPE_KEY = "Content-Type";

		private $mockMiddlewareRegistry, $staticHeaders = [];

		public function withHeaders(array $headers):self {

			$this->staticHeaders = array_merge($this->staticHeaders, $headers);

			return $this;
		}

		public function withToken(string $token, string $type = "Bearer"):self {

			$this->staticHeaders["Authorization"] = $type . " " . $token;

			return $this;
		}

		protected function firstModuleContainer ():Container {

			return $this->entrance->firstContainer();
		}

		protected function getInitializerWrapper ():ModuleToRoute {

			return $this->firstModuleContainer()->getClass(ModuleToRoute::class);
		}

		protected function activeModuleContainer ():Container {

			$activeModule = $this->getInitializerWrapper()->getActiveModule();

			if (!is_null($activeModule)) // if [gatewayResponse] has not been called

				return $activeModule->getContainer();

			return $this->firstModuleContainer();
		}

		public function from (string $url):self {

			$this->setHttpParams($url);

			$initializer = $this->getInitializerWrapper()

			->findContext($this->getModules());

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

			$this->assertEmpty(array_diff($middlewares, $matches));
		}

		protected function assertDidntUseMiddleware (array $middlewares) {

			$this->assertEmpty($this->getMatchingMiddleware($middlewares));
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

		public function get(string $url, array $headers = []):TestResponse {

			return $this->gatewayResponse($url, __FUNCTION__, null, $headers);
		}

		public function getJson(string $url, array $headers = []):TestResponse {

			return $this->json( "get", $url, null, $headers);
		}

		private function gatewayResponse (string $requestPath, string $httpMethod, ?string $payload, array $headers):TestResponse {

			$entrance = $this->entrance;

			$this->setHttpParams($requestPath, $httpMethod, $payload, $headers);

			$entrance->diffusedRequestResponse();

			$renderer = $entrance->underlyingRenderer();

			return $this->makeExaminable($renderer);
		}

		public function post(string $url, array $payload = [], array $headers = []):TestResponse {

			$newPayload = $this->payloadStringifier($payload, $headers);

			return $this->gatewayResponse($url, __METHOD__, $newPayload, $headers);
		}

		public function postJson(string $url, array $payload = [], array $headers = []):TestResponse {

			return $this->json("post", $url, $payload, $headers);
		}

		public function put(string $url, array $payload = [], array $headers = []):TestResponse {

			$newPayload = $this->payloadStringifier($payload, $headers);

			return $this->gatewayResponse($url, __METHOD__, $newPayload, $headers);
		}

		public function putJson(string $url, array $payload = [], array $headers = []):TestResponse {

			return $this->json("put", $url, $payload, $headers);
		}

		public function delete(string $url, array $payload = [], array $headers = []):TestResponse {

			$newPayload = $this->payloadStringifier($payload, $headers);

			return $this->gatewayResponse($url, __METHOD__, $newPayload, $headers);
		}

		public function deleteJson(string $url, array $payload = [], array $headers = []):TestResponse {

			return $this->json("delete", $url, $payload, $headers);
		}

		public function json(string $httpMethod, string $url, array $payload = [], array $headers = []):TestResponse {

			$converted = json_encode($payload);

			$newHeaders = array_merge([
				"Content-Length" => mb_strlen($converted, "8bit"),

				$this->CONTENT_TYPE_KEY => $this->JSON_HEADER_VALUE,

				"Accept" => $this->JSON_HEADER_VALUE
			], $headers);

			return $this->gatewayResponse($url, $httpMethod, $converted, $newHeaders);
		}

		private function payloadStringifier (array $payload, array $headers):string {

			if (
				array_key_exists(self::CONTENT_TYPE_KEY, $headers) &&

				$headers[$this->CONTENT_TYPE_KEY] == $this->JSON_HEADER_VALUE
			)

				return json_encode($payload);

			return http_build_query($payload);
		}
	}
?>