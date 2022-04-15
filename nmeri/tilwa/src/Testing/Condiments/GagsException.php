<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Modules\ModuleExceptionBridge;

	use Tilwa\Hydration\Container;

	use Throwable;

	trait GagsException {

		private $exceptionBroadcaster,

		$alerterMethod = "queueAlertAdapter",

		$exceptionBroadcasterName = DetectedExceptionManager::class,

		$exceptionBridge = ModuleExceptionBridge::class;

		protected $muffleExceptionBroadcast = true,

		$debugCaughtExceptions = false;

		protected function setUp () {

			$this->setBroadcaster();

			$this->provideExceptionObjects();
		}

		private function setBroadcaster ():void {

			$stubs = [];

			if ($this->muffleExceptionBroadcast)

				$stubs["queueAlertAdapter"] = null;

			$this->exceptionBroadcaster = $this->positiveDouble($this->exceptionBroadcasterName, $stubs); // set it to a local instance to enable dev to run tests like [assertWillCatchPayload]
		}

		protected function provideExceptionObjects ():void {

			$this->massProvide([

				$this->exceptionBridge => $this->getExceptionBridge(),

				$this->exceptionBroadcasterName => $this->exceptionBroadcaster
			]);
		}

		protected function getExceptionBridge ():ModuleExceptionBridge {

			$parameters = $this->getContainer()->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, $this->exceptionBridge
			);

			$parameters[ "exceptionDetector"] = $this->exceptionBroadcaster;

			$methods = [
				"writeStatusCode" => null,

				"disgracefulShutdown" => $this->returnCallback(function ($argument) {

					return "hi";
				}),

				"gracefulShutdown" => $this->returnArgument(0)
			];

			if ($this->debugCaughtExceptions)

				$methods["hydrateHandler"] = $this->returnCallback(function ($argument) {

					throw $argument;
				});

			return $this->replaceConstructorArguments(

				$this->exceptionBridge, $parameters,

				$methods
			);
		}

		protected function assertWillCatchPayload ($payload) {

			$sut = $this->mockCalls([

				$this->alerterMethod => [1, [

					$this->callback(function ($subject) {

						return $subject instanceof Throwable;
					}),
					$this->equalTo($payload)
				]]
			], $this->exceptionBroadcaster);

			$this->massProvide([

				$this->exceptionBroadcasterName => $sut
			]);
		}

		protected function assertWillCatchException (string $exception) {

			$sut = $this->mockCalls([

				$this->alerterMethod => [1, [

					$this->callback(function ($subject) use ($exception) {

						return $subject instanceof $exception;
					}),
					$this->anything()
				]]
			], $this->exceptionBroadcaster);

			$this->massProvide([ $this->exceptionBroadcasterName => $sut ]);
		}
	}
?>