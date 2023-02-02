<?php
	namespace Suphle\Bridge\Laravel;

	use Suphle\Contracts\{Config\Laravel, Bridge\LaravelContainer};

	use Suphle\Services\Decorators\BindsAsSingleton;

	use Suphle\Bridge\Laravel\{DefaultExceptionHandler, Config\ConfigLoader};

	use Suphle\Request\{RequestDetails, PayloadStorage};

	use Illuminate\Http\Request;

	use Illuminate\Foundation\Application;

	use Illuminate\Foundation\Bootstrap\{RegisterFacades, RegisterProviders, BootProviders};

	use Illuminate\Contracts\Debug\ExceptionHandler;

	use ReflectionClass;

	#[BindsAsSingleton(LaravelContainer::class)]
	class LaravelAppConcrete extends Application implements LaravelContainer {

		protected const KERNEL_BOOTSTRAPPERS = [

			RegisterFacades::class, RegisterProviders::class,

			BootProviders::class
		];

		private static bool $hasSetApp = false;

		public function __construct (

			protected readonly RequestDetails $requestDetails,

			protected readonly ConfigLoader $configLoader,

			protected readonly PayloadStorage $payloadStorage,

			string $basePath
		) {

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

			foreach (self::KERNEL_BOOTSTRAPPERS as $bootstrapper)

				(new $bootstrapper)->bootstrap($this);
		}

		public function overrideAppHelper ():void {

			if (self::$hasSetApp) return;

			function app () { return $this; }

			self::$hasSetApp = true;
		}
	}
?>