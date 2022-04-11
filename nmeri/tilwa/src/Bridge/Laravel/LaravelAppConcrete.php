<?php
	namespace Tilwa\Bridge\Laravel;

	use Tilwa\Contracts\{Config\Laravel, Bridge\LaravelContainer};

	use Tilwa\Bridge\Laravel\Config\ConfigLoader;

	use Tilwa\Request\{RequestDetails, PayloadStorage};

	use Illuminate\Http\Request;

	use Illuminate\Foundation\Application;

	use Illuminate\Foundation\Bootstrap\{RegisterFacades, RegisterProviders};

	use ReflectionClass;

	class LaravelAppConcrete extends Application implements LaravelContainer {

		private $requestDetails, $configLoader, // these bindings are stored here rather than on the config in order to avoid circular dependencies between that config and configLoader

		$payloadStorage,

		$helpers = [
			"Collections/helpers.php", "Events/functions.php",

			"Foundation/helpers.php", "Support/helpers.php"
		];

		private static $hasSetApp = false;

		protected $kernelBootstrappers = [

			RegisterProviders::class, RegisterFacades::class
		];

		public function __construct (RequestDetails $requestDetails, ConfigLoader $configLoader, PayloadStorage $payloadStorage, string $basePath) {

			$this->configLoader = $configLoader;

			$this->requestDetails = $requestDetails;

			$this->payloadStorage = $payloadStorage;

			parent::__construct($basePath);
		}

		public function defaultBindings ():array {

			return [
				"app" => $this,

				"config" => $this->configLoader,

				"request" => $this->provideRequest()
			];
		}

		public function injectBindings (array $bindings):void {

			foreach ($bindings as $alias => $concrete)

				$this->instance($alias, $concrete);
		}

		protected function provideRequest ():Request {

			return Request::create(
				$this->requestDetails->getPath()?? "", // workable alternative: add Request to list of deferred actions to be taken when url is eventually available

				$this->requestDetails->httpMethod(),

				$this->payloadStorage->fullPayload(),

				$_COOKIE, $_FILES, $_SERVER
			);
		}

		public function runContainerBootstrappers ():void {

			foreach ($this->kernelBootstrappers as $bootstrapper)

				(new $bootstrapper)->bootstrap($this);
		}

		public function createSandbox (callable $explosive) {

			$this->requireHelpers(); // we need this file active while running their routes so it can pick [view()]

			if (!self::$hasSetApp) {

				function app () { // override their definition

					return $this;
				}

				self::$hasSetApp = true;
			}

			$result = $explosive();

			// use get_defined_functions() and possibly reflection to unset functions declared in those files
			return $result;
		}

		private function requireHelpers ():void {

			$knownClass = new ReflectionClass(Application::class);

			$rootArray = explode("\\", $knownClass->getFileName(), -2);

			$packageRoot = implode(DIRECTORY_SEPARATOR, $rootArray);

			foreach ($this->helpers as $relativePath)

				require_once $packageRoot . DIRECTORY_SEPARATOR . $relativePath;
		}
	}
?>