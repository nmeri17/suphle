<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\{Requests\StdInputReader, Auth\UserHydrator};

	use Tilwa\Request\{RequestDetails, PayloadStorage};

	trait DirectHttpTest {

		use MockFacilitator;

		private $HTML_HEADER_VALUE = "application/x-www-form-urlencoded",

		$jsonHeaders = [

			PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::JSON_HEADER_VALUE
		];

		/**
		 * Writes to the superglobals RequestDetails can read from but doesn't actually send any request. Use when we're invoking router/request handler directly
		*/
		protected function setHttpParams (string $requestPath, string $httpMethod = "get", ?array $payload = [], array $headers = []):void {

			$headers["REQUEST_METHOD"] = $httpMethod;

			$reader = ["getHeaders" => $headers];

			$this->loadServerVariables($headers);

			if (!empty($payload) && array_key_exists(PayloadStorage::CONTENT_TYPE_KEY, $headers))

				if ($headers[PayloadStorage::CONTENT_TYPE_KEY] != PayloadStorage::JSON_HEADER_VALUE)

					$_POST = $payload;

				else $reader["getPayload"] = $payload;

			$this->massProvide([

				StdInputReader::class => $this->positiveDouble(StdInputReader::class, $reader)
			]);

			$this->setRequestPath($requestPath);
		}

		abstract protected function setRequestPath (string $requestPath):void;

		protected function setJsonParams (string $requestPath, array $payload, string $httpMethod = "post"):bool {

			if ($this->isValidPayloadType($httpMethod)) {

				$this->setHttpParams($requestPath, $httpMethod, $payload, $this->jsonHeaders);

				return true;
			}

			return false;
		}

		protected function setHtmlForm (string $requestPath, array $payload, string $httpMethod = "post"):bool {

			$headers = [

				PayloadStorage::CONTENT_TYPE_KEY => $this->HTML_HEADER_VALUE
			];

			if ($this->isValidPayloadType($httpMethod)) {

				$this->setHttpParams($requestPath, $httpMethod, $payload, $headers);

				return true;
			}

			return false;
		}

		private function isValidPayloadType (string $httpMethod):bool {

			return in_array($httpMethod, ["post", "put"]);
		}

		private function loadServerVariables (array $headers):void {

			foreach ($headers as $name => $value)

				$_SERVER[$name] = $value;
		}
	}
?>