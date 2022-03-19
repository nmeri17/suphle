<?php
	namespace Tilwa\Testing\Condiments;

	trait DirectHttpTest {

		private $JSON_HEADER_VALUE = "application/json";

		private $HTML_HEADER_VALUE = "application/x-www-form-urlencoded";

		private $CONTENT_TYPE_KEY = "Content-Type";

		/**
		 * Writes to the superglobals RequestDetails can read from but doesn't actually send any request. Use when we're invoking router/request handler directly
		*/
		protected function setHttpParams (string $requestPath, string $httpMethod = "get", ?string $payload = "", array $headers = []):void {

			$components = parse_url($requestPath);

			$_GET["tilwa_path"] = $components["path"];

			$_GET = array_merge($_GET, $components["query"] ?? []);

			$_SERVER["REQUEST_METHOD"] = $httpMethod;

			$_SERVER = array_merge($_SERVER, $headers);

			if (!empty($payload) && array_key_exists($this->CONTENT_TYPE_KEY, $headers))

				$this->writePayload($payload, $headers[$this->CONTENT_TYPE_KEY]);
		}

		protected function setJsonParams (string $requestPath, array $payload, string $httpMethod = "post"):bool {

			$headers = [$this->CONTENT_TYPE_KEY => $this->JSON_HEADER_VALUE];

			if ($this->isValidPayloadType($httpMethod)) {

				$this->setHttpParams($requestPath, $httpMethod, json_encode($payload), $headers);

				return true;
			}

			return false;
		}

		protected function setHtmlForm (string $requestPath, array $payload, string $httpMethod = "post"):bool {

			$headers = [$this->CONTENT_TYPE_KEY => $this->HTML_HEADER_VALUE];

			if ($this->isValidPayloadType($httpMethod)) {

				$this->setHttpParams($requestPath, $httpMethod, http_build_query($payload), $headers);

				return true;
			}

			return false;
		}

		private function isValidPayloadType (string $httpMethod):bool {

			return in_array($httpMethod, ["post", "put"]);
		}

		private function writePayload (string $payload, string $contentType):void {

			if ($contentType != $this->JSON_HEADER_VALUE)

				$_POST = $payload;

			else file_put_contents("php://output", $payload);
		}
	}
?>