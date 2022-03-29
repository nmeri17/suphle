<?php
	namespace Tilwa\Request;

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

		public function getPath ():?string {

			$pathKey = "tilwa_path";

			if (array_key_exists($pathKey, $_GET)) // leave it open to be set multiple since framework can be run in contexts where url isn't the first thing to happen e.g. some tests where database is setup before receiving url

				$this->path = $_GET[$pathKey];

			return $this->path;
		}

		public function httpMethod ():string {

			return strtolower(

				$_POST["_method"] ?? @$_SERVER["REQUEST_METHOD"]
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

			$versionKeys = array_map("strtolower", array_keys($apiStack) );

			$versionHandlers = array_values($apiStack);

			$versionPresent = strtolower($this->incomingVersion()); // case-insensitive search

			$versionCount = count($versionHandlers) - 1;

			// if there's no specific version, we will serve the most recent
			if ( !empty($versionPresent))

				$startIndex = array_search($versionPresent, $versionKeys);

			else $startIndex = $versionCount;

			$versionHandlers = array_slice($versionHandlers, $startIndex, $versionCount);

			$versionKeys = array_slice($versionKeys, $startIndex, $versionCount);

			return array_combine($versionKeys, $versionHandlers);
		}

		public function matchesMethod (string $method):bool {

			return $this->httpMethod() == $method;
		}

		public function matchingPath (string $path):string {

			$sanitizedPath = preg_quote(trim($path, "/"), "/");
// var_dump($sanitizedPath, preg_match("/\/?" . $sanitizedPath . "\/?/i", $this->path), $this->path);
			return preg_match("/\/?" . $sanitizedPath . "\/?/i", $this->path);
		}
	}
?>