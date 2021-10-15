<?php
	namespace Tilwa\Testing;

	trait FrontDoorTest {

		use DirectHttpTest;

		private $entrance;

		public function setUp () {

			$this->entrance = new FrontDoor($this->getModules());
		}
		
		/**
		 * @return ModuleDescriptor[]
		 */
		abstract protected function getModules():array;

		// convert these guys to the http whatever they have. then update usages of these guys to the new names
		protected function httpGet(string $url) {

			$this->setHttpParams($url);

			$response = $this->getEntrance()->orchestrate();

			return $this->unserializeResult($response);
		}

		protected function httpPostJson (string $requestPath, array $payload, string $httpMethod = "post") {

			$this->setJsonParams($requestPath, $payload, $httpMethod);

			return $this->entrance->orchestrate();
		}

		protected function httpPostForm (string $requestPath, array $payload, string $httpMethod = "post") {

			$this->setHtmlForm($requestPath, $payload, $httpMethod);

			return $this->entrance->orchestrate();
		}

		private function unserializeResult ($response) {

			// if accept=json, json_decode
		}
	}
?>