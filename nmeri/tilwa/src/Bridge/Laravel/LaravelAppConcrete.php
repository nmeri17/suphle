<?php
	namespace Tilwa\Bridge\Laravel;

	use Tilwa\Contracts\{Config\Laravel, Bridge\LaravelContainer};

	use Tilwa\Bridge\Laravel\Config\ConfigLoader;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Request\PayloadStorage;

	use Illuminate\{Http\Request, Foundation\Application};

	class LaravelAppConcrete extends Application implements LaravelContainer {

		private $requestDetails, $configLoader, // these bindings are stored here rather than on the config in order to avoid circular dependencies between that config and configLoader

		$payloadStorage;

		public function __construct (RequestDetails $requestDetails, ConfigLoader $configLoader, PayloadStorage $payloadStorage) {

			$this->configLoader = $configLoader;

			$this->requestDetails = $requestDetails;

			$this->payloadStorage = $payloadStorage;
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
	}
?>