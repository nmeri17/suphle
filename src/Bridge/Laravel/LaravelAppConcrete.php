<?php
	namespace Suphle\Bridge\Laravel;

	use Suphle\Contracts\{Config\Laravel, Bridge\LaravelContainer};

	use Suphle\Services\Decorators\BindsAsSingleton;

	use Suphle\Bridge\Laravel\{DefaultExceptionHandler, Config\ConfigLoader};

	use Suphle\Request\{RequestDetails, PayloadStorage};

	use Illuminate\Http\Request as LaravelRequest;

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

		public function protectRefreshPurge ():bool {

			return true;
		}

		public function concreteBinds ():array {

			return [
				"app" => $this,

				"config" => $this->configLoader,

				LaravelContainer::INCOMING_REQUEST_KEY => $this->provideRequest(
				
					$this->requestDetails, $this->payloadStorage
				)
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

		public function provideRequest (

			RequestDetails $requestDetails, PayloadStorage $payloadStorage
		):LaravelRequest {

			return LaravelRequest::create(
				$requestDetails->getPath()?? "", // in case this container is requested outside a http context

				$requestDetails->httpMethod(),

				$payloadStorage->fullPayload(),

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