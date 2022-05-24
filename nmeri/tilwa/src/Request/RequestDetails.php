<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Hydration\Container;

	/**
	 * Our closest adaptation of the PSR\RequestInterface
	*/
	class RequestDetails {

		private $config, $computedPath, $permanentPath // readonly version of [computedPath]
		;

		public function __construct (Router $config) {

			$this->config = $config;
		}

		public function getPath ():?string {

			return $this->computedPath;
		}

		public function setPath (string $requestPath):void {

			$this->permanentPath = $this->computedPath = $requestPath;
		}

		public static function fromModules (array $descriptors, string $requestPath):void {

			foreach ($descriptors as $descriptor)

				static::fromContainer($descriptor->getContainer(), $requestPath);
		}

		public static function fromContainer (Container $container, string $requestPath):void {

			$selfName = get_called_class();

			$container->refreshClass($selfName);

			$instance = $container->getClass($selfName);

			$components = parse_url($requestPath);

			$instance->setPath($components["path"]);

			$container->whenTypeAny()->needsAny([

				$selfName => $instance
			]);

			parse_str($components["query"] ?? "", $queryArray);

			$_GET = array_merge($_GET, $queryArray);
		}

		public function getPermanentPath ():?string {

			return $this->permanentPath;
		}

		public function httpMethod ():string {

			$hiddenField = "_method";

			$serverField = "REQUEST_METHOD";

			if (array_key_exists($hiddenField, $_POST))

				$methodName = $_POST[$hiddenField];

			elseif (array_key_exists($serverField, $_SERVER))

				$methodName = $_SERVER[$serverField];

			else $methodName = "get";

			return strtolower($methodName);
		}

		public function isGetRequest ():bool {

			return $this->httpMethod() == "get";
		}

		public function isPostRequest ():bool {

			return $this->httpMethod() == "post";
		}

		private function regexApiPrefix ():string {

			return "^\/?" . $this->config->apiPrefix();
		}

		public function isApiRoute ():bool {

			return preg_match("/" . $this->regexApiPrefix() . "/", $this->permanentPath); // using permanent since computed may have been changed by the time this method is being read
		}

		// given a request to api/v3/verb/noun, return verb/noun
		public function stripApiPrefix():void {
			
			$pattern = $this->regexApiPrefix() . "\/.+?\/(.+)";

			preg_match("/" . $pattern . "/i", $this->permanentPath, $pathArray);

			$this->computedPath = $pathArray[1];
		}

		// given a request to api/v3/verb/noun, return v3
		private function incomingVersion():string {
			
			$pattern = $this->regexApiPrefix() . "\/(.+?)\/";

			preg_match("/" . $pattern . "/i", $this->permanentPath, $version);

			return $version[1];
		}

		# api/v3/verb/noun should return all versions from v3 and below
		public function apiVersionClasses ():array {

			$apiStack = $this->config->apiStack();

			$versionKeys = array_map("strtolower", array_keys($apiStack) );

			$versionHandlers = array_values($apiStack);

			$versionPresent = strtolower($this->incomingVersion()); // case-insensitive search

			// if there's no specific version, we will serve the most recent
			if ( !empty($versionPresent))

				$startIndex = array_search($versionPresent, $versionKeys);

			else $startIndex = count($versionHandlers) - 1;

			$versionHandlers = array_slice($versionHandlers, $startIndex);

			$versionKeys = array_slice($versionKeys, $startIndex);

			return array_combine($versionKeys, $versionHandlers);
		}

		public function matchesMethod (string $method):bool {

			return $this->httpMethod() == $method;
		}

		public function matchesPath (string $path):bool {

			$sanitizedPath = preg_quote(trim($path, "/"), "/");

			return preg_match("/^\/?" . $sanitizedPath . "\/?$/i", $this->getPath());
		}
	}
?>