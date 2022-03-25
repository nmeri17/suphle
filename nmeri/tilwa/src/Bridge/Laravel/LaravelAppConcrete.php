<?php
	namespace Tilwa\Bridge\Laravel;

	use Tilwa\Contracts\{Config\Laravel, Bridge\LaravelContainer};

	use Tilwa\Bridge\Laravel\Config\ConfigLoader;

	use Tilwa\Request\{RequestDetails, PayloadStorage};

	use Illuminate\Http\Request;

	use Illuminate\Foundation\Application;

	use Illuminate\Foundation\Bootstrap\{RegisterFacades, RegisterProviders};

	class LaravelAppConcrete extends Application implements LaravelContainer {

		private $requestDetails, $configLoader, // these bindings are stored here rather than on the config in order to avoid circular dependencies between that config and configLoader

		$payloadStorage;

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
				$this->requestDetails->getPath(),

				$this->requestDetails->httpMethod(),

				$this->payloadStorage->fullPayload(),

				$_COOKIE, $_FILES, $_SERVER
			);
		}

		public function runContainerBootstrappers ():void {

			foreach ($this->kernelBootstrappers as $bootstrapper)

				(new $bootstrapper)->bootstrap($this);
		}
	}
?>