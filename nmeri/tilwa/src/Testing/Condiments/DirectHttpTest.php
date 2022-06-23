<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\Requests\{StdInputReader, FileInputReader};

	use Tilwa\Contracts\Auth\UserHydrator;

	use Tilwa\Request\{RequestDetails, PayloadStorage};

	use Tilwa\Testing\Proxies\Extensions\InjectedUploadedFiles;

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

			$headers[RequestDetails::HTTP_METHOD_KEY] = $httpMethod;

			$reader = ["getHeaders" => $headers];

			if (
				!empty($payload) &&

				$this->isValidPayloadType($httpMethod)
			) {

				$hasHeader = array_key_exists(

					PayloadStorage::CONTENT_TYPE_KEY, $headers
				);

				if ($hasHeader && $headers[PayloadStorage::CONTENT_TYPE_KEY] != PayloadStorage::JSON_HEADER_VALUE)

					$_POST = $payload;

				else $reader["getPayload"] = $payload;
			}
var_dump(39, $reader, $payload, $_POST);
			$this->massProvide([

				StdInputReader::class => $this->positiveDouble(StdInputReader::class, $reader)
			]);

			$this->setRequestPath($requestPath);
		}

		/**
		 * @param {files} SplFileInfo[]
		*/
		protected function provideFileObjects (array $files, string $httpMethod):void {

			if (!$this->isValidPayloadType($httpMethod)) return;

			$this->massProvide([

				FileInputReader::class => new InjectedUploadedFiles($files)
			]);
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
	}
?>