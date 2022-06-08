<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Modules\ModuleExceptionBridge;

	use Tilwa\Hydration\Container;

	use PHPUnit\Framework\MockObject\Stub\Stub;

	trait GagsException {

		private $bridgeName = ModuleExceptionBridge::class;

		protected $muffleExceptionBroadcast = true,

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

			$this->getContainer()->whenTypeAny()->needsAny([

				$broadcasterName => $this->replaceConstructorArguments($broadcasterName, [], $stubs)
			]);
		}

		protected function provideExceptionBridge (array $bridgeStubs):void {

			$this->massProvide([

				$this->bridgeName => $this->constructExceptionBridge($bridgeStubs)
			]);
		}

		protected function exceptionBridgeStubs ():array {

			return [
				"disgracefulShutdown" => $this->getDisgracefulShutdown(),

				"gracefulShutdown" => $this->getGracefulShutdown()
			];
		}

		protected function getDisgracefulShutdown ():Stub {

			return $this->returnCallback(function ($argument) {
var_dump($argument);
				return "GagsException->getDisgracefulShutdown";
			});
		}

		protected function getGracefulShutdown ():Stub {

			return $this->returnCallback(function ($argument) {

				return json_decode($argument, true);
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