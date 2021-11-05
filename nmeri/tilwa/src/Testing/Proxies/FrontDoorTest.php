<?php
	namespace Tilwa\Testing\Proxies;

	use Illuminate\Testing\TestResponse;

	use Tilwa\Testing\Condiments\DirectHttpTest;

	use Tilwa\Testing\Proxies\Extensions\FrontDoor;

	use Tilwa\App\{Container, ModuleToRoute};

	/**
	 * Useful when running http tests that need to go through app entry point and get handled end-to-end
	*/
	trait FrontDoorTest {

		use DirectHttpTest, ExaminesHttpResponse;

		const JSON_HEADER_VALUE = "application/json";

		private $entrance, $staticHeaders = [],

		$contentTypeKey = "Content-Type";

		public function setUp () {

			$this->entrance = new FrontDoor($this->getModules());
		}
		
		/**
		 * @return ModuleDescriptor[]
		 */
		abstract protected function getModules():array;

	    public function withHeaders(array $headers):self {

	    	$this->staticHeaders = array_merge($this->staticHeaders, $headers);

	    	return $this;
	    }

	    public function withToken(string $token, string $type = "Bearer"):self {

	    	$this->staticHeaders["Authorization"] = $type . " " . $token;

	    	return $this;
	    }

		protected function getContainer ():Container {

			return $this->entrance->firstContainer();
		}

	    public function from(string $url):self {

	    	$this->setHttpParams($url);

	    	$initializer = $this->getContainer()->getClass(ModuleToRoute::class)

	    	->findContext($this->getModules());

	    	$router = $initializer->getRouter();

	    	$renderer = $router->setPreviousRenderer($router->getActiveRenderer());

	    	return $this;
	    }

	    public function withoutMiddleware($middleware = null):self {

	    	return $this;
	    }

	    public function withMiddleware($middleware = null):self {

	    	return $this;
	    }

	    public function get(string $url, array $headers = []):TestResponse {

	    	return $this->gatewayResponse($url, __FUNCTION__, null, $headers);
	    }

	    public function getJson(string $url, array $headers = []):TestResponse {

	    	return $this->json( "get", $url, null, $headers);
	    }

	    private function gatewayResponse (string $requestPath, string $httpMethod, ?string $payload, array $headers):TestResponse {

			$entrance = $this->entrance;

			$this->setHttpParams($url, $httpMethod, $payload, $headers);

			$entrance->orchestrate();

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

	            $this->contentTypeKey => self::JSON_HEADER_VALUE,

	            "Accept" => self::JSON_HEADER_VALUE
	        ], $headers);

	    	return $this->gatewayResponse($url, $httpMethod, $converted, $newHeaders);
	    }

	    private function payloadStringifier (array $payload, array $headers):string {

	    	if (array_key_exists($this->contentTypeKey, $headers) && $headers[$this->contentTypeKey] == self::JSON_HEADER_VALUE)

	    		return json_encode($payload);

	    	return http_build_query($payload);
	    }
	}
?>