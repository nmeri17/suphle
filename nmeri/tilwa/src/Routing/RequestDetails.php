<?php

	namespace Tilwa\Routing; // relocate this to Request namespace

	use Tilwa\Contracts\Config\Router;

	/**
	 * Suffices for components without access to their own handlers i.e. login
	 * 
	 * Our closest adaptation of the PSR\RequestInterface
	*/
	class RequestDetails {

		private $config, $path;

		public function __construct (Router $config) {

			$this->config = $config;
		}

		public function getPath ():string {

			$pathKey = "tilwa_path";

			if (is_null($this->path)) {

				$this->path = $_GET[$pathKey];

				unset($_GET[$pathKey]);
			}

			return $this->path;
		}

		public function httpMethod ():string {

			return strtolower(

				$_POST["_method"] ?? $_SERVER['REQUEST_METHOD']
			);
		}

		public function isGetRequest ():bool {

			return $this->httpMethod() == "get";
		}

		public function isPostRequest ():bool {

			return $this->httpMethod() == "post";
		}

		public function isApiRoute ():bool {

			return preg_match("/^" . $this->config->apiPrefix() . "/", $this->path);
		}

		// given a request to api/v3/verb/noun, return verb/noun
		public function stripApiPrefix():void {
			
			$pattern = $this->config->apiPrefix() . "\/.+?\/(.+)";

			preg_match("/^" . $pattern . "/i", $this->path, $pathArray);
			
			$this->path = $pathArray[1];
		}

		// given a request to api/v3/verb/noun, return v3
		private function incomingVersion():string {
			
			$pattern = $this->config->apiPrefix() . "\/(.+?)\/";

			preg_match("/^" . $pattern . "/i", $this->path, $version);

			return $version[1];
		}

		# api/v3/verb/noun should return all versions from v3 and below
		public function apiVersionClasses():array {

			$apiStack = $this->config->apiStack();

			$versionKeys = array_keys($apiStack);

			$versionHandlers = array_values($apiStack);

			$start = array_search( // case-insensitive search

				strtolower($this->incomingVersion()),

				array_map("strtolower", $versionKeys)
			);

			$versionHandlers = array_slice($versionHandlers, $start, count($versionHandlers)-1);

			$versionKeys = array_slice($versionKeys, $start, count($versionKeys)-1);

			return array_combine($versionKeys, $versionHandlers);
		}

		public function matchesMethod (string $name):bool {

			return $this->httpMethod() == $name;
		}
	}
?>