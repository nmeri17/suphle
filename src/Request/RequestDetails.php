<?php
	namespace Suphle\Request;

	use Suphle\Contracts\{Config\Router, Requests\StdInputReader};

	use Suphle\Services\Decorators\BindsAsSingleton;

	use Suphle\Hydration\Container;

	use InvalidArgumentException;

	/**
	 * Our closest adaptation of the PSR\RequestInterface
	*/
	#[BindsAsSingleton]
	class RequestDetails {

		final const HTTP_METHOD_KEY = "HTTP_METHOD";

		protected ?string $computedPath = null,

		$versionPresent, $permanentPath = null; // readonly version of [computedPath]

		protected array $queryParameters = [];

		public function __construct(

			protected readonly Router $config,

			protected readonly StdInputReader $stdInputReader
		) {

			//
		}

		public function getPath ():?string {

			return $this->computedPath;
		}

		public function setPath (string $requestPath):void {

			$this->permanentPath = $this->computedPath = $requestPath;
		}

		public function setQueries (array $queryParameters):void {

			$this->queryParameters = $queryParameters;
		}

		public function getQueryParameters ():array {

			return $this->queryParameters;
		}

		public static function fromModules (array $descriptors, string $requestPath):void {

			foreach ($descriptors as $descriptor)

				static::fromContainer($descriptor->getContainer(), $requestPath);
		}

		public static function fromContainer (Container $container, string $requestPath):?self {

			$components = parse_url($requestPath);

			$pathComponent = @$components["path"];

			if (is_null($pathComponent)) return null;

			$selfName = self::class;

			$container->refreshClass($selfName);

			$instance = $container->getClass($selfName);

			$instance->setPath($pathComponent);

			parse_str($components["query"] ?? "", $queryParameters);

			$instance->setQueries($queryParameters);

			return $instance;
		}

		public function getPermanentPath ():?string {

			return $this->permanentPath;
		}

		public function httpMethod ():string {

			$hiddenField = "_method";

			$headers = $this->stdInputReader->getHeaders();

			if (array_key_exists($hiddenField, $_POST))

				$methodName = $_POST[$hiddenField];

			elseif (array_key_exists(self::HTTP_METHOD_KEY, $headers))

				$methodName = $headers[self::HTTP_METHOD_KEY];

			else $methodName = "get";

			return strtolower((string) $methodName);
		}

		public function matchesMethod (string $method):bool {

			return preg_match("/" . $this->httpMethod() . "/i", $method);
		}

		public function isGetRequest ():bool {

			return $this->matchesMethod("get");
		}

		public function isPostRequest ():bool {

			return $this->matchesMethod("post");
		}

		private function regexApiPrefix ():string {

			return "^\/?" . $this->config->apiPrefix();
		}

		public function isApiRoute ():bool {

			$matches = preg_match("/" . $this->regexApiPrefix() . "/", (string) $this->permanentPath); // using permanent since computed may have been changed by the time this method is being read

			if ($matches) $this->setIncomingVersion();

			return $matches;
		}

		/**
		* Given a request to api(?:/v3)/verb/noun, set computed path to verb/noun
		*/
		public function stripApiPrefix():void {

			$possibleVersion = $this->versionPresent ?

				"(?:\/". $this->versionPresent .")?":

				"";
			
			$pattern = $this->regexApiPrefix() . $possibleVersion. "\/(.+)";

			preg_match("/" . $pattern . "/i", (string) $this->permanentPath, $pathArray);

			$this->computedPath = $pathArray[1];
		}

		/**
		 * Given a request to api/v3/verb/noun, sets property to v3
		*/
		public function setIncomingVersion ():void {
			
			$pattern = $this->regexApiPrefix() . "\/(.+?)\/";

			preg_match("/" . $pattern . "/i", (string) $this->permanentPath, $version);

			$this->versionPresent = $version[1] ?? null;
		}

		# api/v3/verb/noun or api/verb/noun will return all versions from v3 and below
		public function apiVersionClasses ():array {

			$apiStack = $this->config->apiStack();

			$versionKeys = array_map("strtolower", array_keys($apiStack) );

			$versionHandlers = array_values($apiStack);

			if ( !is_null($this->versionPresent)) {

				$startIndex = array_search(
					strtolower((string) $this->versionPresent), // case-insensitive search
					$versionKeys
				);
			}
			else $startIndex = 0; // if there's no specific version, we will serve the most recent

			$versionHandlers = array_slice($versionHandlers, $startIndex);

			$versionKeys = array_slice($versionKeys, $startIndex);

			return array_combine($versionKeys, $versionHandlers);
		}

		public function matchesPath (string $path):bool {

			$sanitizedPath = preg_quote(trim($path, "/"), "/");

			return preg_match("/^\/?" . $sanitizedPath . "\/?$/i", $this->getPath());
		}
	}
?>