<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Modules\ModuleExceptionBridge;

	use Tilwa\Hydration\Container;

	use PHPUnit\Framework\MockObject\Stub\Stub;

	trait GagsException {

		private $bridgeName = ModuleExceptionBridge::class,

		$broadcasterName = DetectedExceptionManager::class;

		protected $muffleExceptionBroadcast = true,

		$debugCaughtExceptions = false,

		$exceptionBroadcaster, $exceptionBridge;

		protected function setUp () {

			$this->setBroadcaster();

			$this->provideExceptionBridge($this->exceptionBridgeStubs());
		}

		private function setBroadcaster ():void {

			$stubs = [];

			if ($this->muffleExceptionBroadcast)

				$stubs["queueAlertAdapter"] = null;

			$this->getContainer()->whenTypeAny()->needsAny([

				$this->broadcasterName => $this->exceptionBroadcaster = $this->replaceConstructorArguments($this->broadcasterName, [], $stubs)
			]);
		}

		protected function provideExceptionBridge (array $bridgeStubs):void {

			$this->constructExceptionBridge($bridgeStubs);

			$this->massProvide([

				$this->bridgeName => $this->exceptionBridge
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
				return "GagsException->constructExceptionBridge->disgracefulShutdown";
			});
		}

		protected function getGracefulShutdown ():Stub {

			return $this->returnCallback(function ($argument) {

				return json_decode($argument, true);
			});
		}

		private function constructExceptionBridge (array $dynamicStubs):void {

			$defaultStubs = ["writeStatusCode" => null];

			if ($this->debugCaughtExceptions)

				$defaultStubs["hydrateHandler"] = $this->returnCallback(function ($argument) {

					throw $argument;
				});

			$this->exceptionBridge = $this->replaceConstructorArguments(

				$this->bridgeName,

				$this->getContainer()->getMethodParameters(

					Container::CLASS_CONSTRUCTOR, $this->bridgeName
				),

				array_merge($defaultStubs, $dynamicStubs)
			);
		}
	}
?>