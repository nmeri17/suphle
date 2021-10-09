<?php
	namespace Tilwa\Testing;

	trait DirectHttpTest {

		const JSON_HEADER_VALUE = "application/json";

		const HTML_HEADER_VALUE = "application/x-www-form-urlencoded";

		/**
		 * Writes to the superglobals RequestDetails can read from but doesn't actually send any request. Use when we're invoking router/request handler directly
		*/
		protected function setHttpParams (string $requestPath, string $httpMethod = "get", string $payload = "", array $headers = []):void {

			$_GET["tilwa_path"] = $requestPath;

			$_SERVER["REQUEST_METHOD"] = $httpMethod;

			$_SERVER += $headers;

			$this->writePayload($payload, $headers["Content-Type"]);
		}

		protected function sendJsonPayload (string $requestPath, array $payload, string $httpMethod = "post"):bool {

			$contentType = ["Content-Type" => self::JSON_HEADER_VALUE];

			if ($this->isValidPayloadType($httpMethod)) {

				$this->setHttpParams($requestPath, $httpMethod, json_encode($payload), $headers);

				return true;
			}

			return false;
		}

		protected function sendHtmlForm (string $requestPath, array $payload, string $httpMethod = "post"):bool {

			$headers = ["Content-Type" => self::HTML_HEADER_VALUE];

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

			if ($contentType != self::JSON_HEADER_VALUE)

				$_POST = $payload;

			else file_put_contents("php://output", $payload);
		}
	}
?>