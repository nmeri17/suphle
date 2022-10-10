<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Exception\DetectedExceptionManager;

	use Suphle\Modules\ModuleExceptionBridge;

	use Suphle\Hydration\Container;

	use PHPUnit\Framework\MockObject\Stub\Stub;

	/**
	 * Used to configure how shutdown methods react when they receive errors
	*/
	trait ConfigureExceptionBridge {

		private $bridgeName = ModuleExceptionBridge::class;

		/**
		 * Set to false when debugging a construct that directly interacts with DetectedExceptionManager::ALERTER_METHOD
		*/
		protected $muffleExceptionBroadcast = true,

		/**
		 * Only applicable when making HTTP requests using get,post etc
		*/
		$debugCaughtExceptions = false;

		protected function setUp () {

			$this->setBroadcaster();

			$this->provideExceptionBridge($this->exceptionBridgeStubs());
		}

		private function setBroadcaster ():void {

			$stubs = [];

			$broadcasterName = DetectedExceptionManager::class;

			if ($this->muffleExceptionBroadcast)

				$stubs[DetectedExceptionManager::ALERTER_METHOD] = null;

			else $stubs[DetectedExceptionManager::ALERTER_METHOD] = $this->returnCallback(function ($exception) {

				throw $exception;
			});

			$container = $this->getContainer();

			$parameters = $container->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, DetectedExceptionManager::class
			);

			$broadcasterInstance = $this->replaceConstructorArguments(

				$broadcasterName, $parameters, $stubs
			);

			$container->whenTypeAny()->needsAny([

				$broadcasterName => $broadcasterInstance
			]);
		}

		protected function provideExceptionBridge (array $bridgeStubs):void {

			$this->massProvide([

				$this->bridgeName => $this->constructExceptionBridge($bridgeStubs)
			]);
		}

		/**
		 * This is the method user likely wants to override
		*/
		protected function exceptionBridgeStubs ():array {

			return [
				"disgracefulShutdown" => $this->getDisgracefulShutdown(),

				"gracefulShutdown" => $this->getGracefulShutdown()
			];
		}

		/**
		 * Dumps error received if graceful fails
		*/
		protected function getDisgracefulShutdown ():Stub {

			return $this->returnCallback(function ($originalError, $gracefulError) {

				var_dump($originalError, $gracefulError);

				return "ConfigureExceptionBridge->getDisgracefulShutdown";
			});
		}

		/**
		 * Returns a callback that skips all the protocols of handling this nicely and returns error received
		*/
		protected function getGracefulShutdown ():Stub {

			return $this->returnCallback(function ($argument) {

				return $argument;
			});
		}

		private function constructExceptionBridge (array $dynamicStubs):ModuleExceptionBridge {

			$defaultStubs = ["writeStatusCode" => null];

			if ($this->debugCaughtExceptions)

				$defaultStubs["hydrateHandler"] = $this->returnCallback(function ($argument) {

					throw $argument;
				});

			return $this->replaceConstructorArguments(

				$this->bridgeName,

				$this->getContainer()->getMethodParameters(

					Container::CLASS_CONSTRUCTOR, $this->bridgeName
				),

				array_merge($defaultStubs, $dynamicStubs)
			);
		}
	}
?>