<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Hydration\Container;

	use Suphle\Modules\ModuleToRoute;

	use Suphle\Middleware\MiddlewareRegistry;

	use Suphle\Request\PayloadStorage;

	use Suphle\Testing\Condiments\DirectHttpTest;

	use Suphle\Testing\Proxies\Extensions\{TestResponseBridge, MiddlewareManipulator};

	use Suphle\Exception\Explosives\NotFoundException;

	trait ModuleHttpTest {

		use DirectHttpTest, ExaminesHttpResponse;

		private array $staticHeaders = [];
		
		private $mockMiddlewareRegistry;

		public function withHeaders(array $headers):self {

			$this->staticHeaders = array_merge($this->staticHeaders, $headers);

			return $this;
		}

		public function withToken(string $token, string $type = "Bearer"):self {

			$this->staticHeaders["Authorization"] = $type . " " . $token;

			return $this;
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

				$this->mockMiddlewareRegistry = $this->getContainer()->getClass(MiddlewareManipulator::class);

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

			$converted = json_encode($payload, JSON_THROW_ON_ERROR);

			$newHeaders = array_merge([
				"Content-Length" => mb_strlen($converted, "8bit"),

				PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE,

				PayloadStorage::ACCEPTS_KEY => PayloadStorage::JSON_HEADER_VALUE
			], $headers);

			return $this->gatewayResponse(

				$url, $httpMethod, $payload, $newHeaders, $files
			);
		}
	}
?>