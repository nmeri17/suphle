<?php
	namespace Suphle\Request;

	use Suphle\Contracts\{Config\Router, Requests\StdInputReader, Services\Decorators\BindsAsSingleton};

	use Suphle\Hydration\Container;

	use DeepCopy\DeepCopy;

	use InvalidArgumentException;

	/**
	 * Our closest adaptation of the PSR\RequestInterface
	*/
	class RequestDetails implements BindsAsSingleton {

		const HTTP_METHOD_KEY = "HTTP_METHOD";

		private $config, $computedPath, $permanentPath, // readonly version of [computedPath]
		
		$versionPresent, $stdInputReader, $queryParameters = [];

		public function __construct (Router $config, StdInputReader $stdInputReader) {

			$this->config = $config;

			$this->stdInputReader = $stdInputReader;
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

		public function entityIdentity ():string {

			return self::class;
		}

		/**
		 * This doesn't modify original descriptors but returns a new set to work with
		 * 
		 * @return ModuleDescriptor[]
		*/
		public static function fromModules (array $descriptors, string $requestPath):array {

			return array_map(function ($descriptor) use ($requestPath) {

				$outgoingContainer = $descriptor->getContainer();

				$mintedDescriptor = (new DeepCopy)->copy($descriptor); // this enables us replace its container as many times as necessary without affecting the original

				$mintedContainer = $mintedDescriptor->getContainer();

				RequestDetails::fromContainer(

					$mintedContainer, $requestPath
				);

				$mintedContainer->setMemoryScope($outgoingContainer);

				return $mintedDescriptor;
			}, $descriptors);
		}

		public static function fromContainer (Container $container, string $requestPath):?self { // modify usages

			$components = parse_url($requestPath);

			$pathComponent = @$components["path"];
$container->lynx = 12; // only the double should get here
			if (is_null($pathComponent)) return null;

			$requestInstance = $container->getClass(get_called_class());

			$requestInstance->setPath($pathComponent);

			parse_str($components["query"] ?? "", $queryParameters);

			$requestInstance->setQueries($queryParameters);

			return $requestInstance;
		}

		public function getPermanentPath ():?string {

			return $this->permanentPath;
		}

		public function httpMethod ():string { // remember to set std in main

			$hiddenField = "_method";

			$headers = $this->stdInputReader->getHeaders();

			if (array_key_exists($hiddenField, $_POST))

				$methodName = $_POST[$hiddenField];

			elseif (array_key_exists(self::HTTP_METHOD_KEY, $headers))

				$methodName = $headers[self::HTTP_METHOD_KEY];

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

			$matches = preg_match("/" . $this->regexApiPrefix() . "/", $this->permanentPath); // using permanent since computed may have been changed by the time this method is being read

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

			preg_match("/" . $pattern . "/i", $this->permanentPath, $pathArray);

			$this->computedPath = $pathArray[1];
		}

		/**
		 * Given a request to api/v3/verb/noun, sets property to v3
		*/
		public function setIncomingVersion ():void {
			
			$pattern = $this->regexApiPrefix() . "\/(.+?)\/";

			preg_match("/" . $pattern . "/i", $this->permanentPath, $version);

			$this->versionPresent = $version[1] ?? null;
		}

		# api/v3/verb/noun or api/verb/noun will return all versions from v3 and below
		public function apiVersionClasses ():array {

			$apiStack = $this->config->apiStack();

			$versionKeys = array_map("strtolower", array_keys($apiStack) );

			$versionHandlers = array_values($apiStack);

			if ( !is_null($this->versionPresent)) {

				$startIndex = array_search(
					strtolower($this->versionPresent), // case-insensitive search
					$versionKeys
				);
			}
			else $startIndex = 0; // if there's no specific version, we will serve the most recent

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