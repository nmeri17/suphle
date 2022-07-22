<?php
	namespace Suphle\Bridge\Laravel;

	use Suphle\Contracts\{Config\Laravel, Bridge\LaravelContainer};

	use Suphle\Bridge\Laravel\{DefaultExceptionHandler, Config\ConfigLoader};

	use Suphle\Request\{RequestDetails, PayloadStorage};

	use Illuminate\Http\Request;

	use Illuminate\Foundation\Application;

	use Illuminate\Foundation\Bootstrap\{RegisterFacades, RegisterProviders, BootProviders};

	use Illuminate\Contracts\Debug\ExceptionHandler;

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

			RegisterFacades::class, RegisterProviders::class,

			BootProviders::class
		];

		public function __construct (RequestDetails $requestDetails, ConfigLoader $configLoader, PayloadStorage $payloadStorage, string $basePath) {

			$this->configLoader = $configLoader;

			$this->requestDetails = $requestDetails;

			$this->payloadStorage = $payloadStorage;

			parent::__construct($basePath);
		}

		public function concreteBinds ():array {

			return [
				"app" => $this,

				"config" => $this->configLoader,

				"request" => $this->provideRequest()
			];
		}

		public function simpleBinds ():array {

			return [

				ExceptionHandler::class => DefaultExceptionHandler::class
			];
		}

		public function registerConcreteBindings (array $bindings):void {

			foreach ($bindings as $alias => $concrete)

				$this->instance($alias, $concrete);
		}

		public function registerSimpleBindings (array $bindings):void {

			foreach ($bindings as $alias => $concrete)

				$this->singleton($alias, $concrete);
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

		public function ensureHasLoadedHelpers ():void {

			if (self::$hasSetApp) return;

			$this->requireHelpers();

			function app () { // override their definition

				return $this;
			}

			self::$hasSetApp = true;
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