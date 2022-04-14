<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Modules\ModuleExceptionBridge;

	use Throwable;

	trait GagsException {

		private $exceptionBroadcaster,

		$alerterMethod = "queueAlertAdapter",

		$exceptionBroadcasterName = DetectedExceptionManager::class,

		$exceptionBridge = ModuleExceptionBridge::class;

		protected $muffleExceptionBroadcast = true;

		protected function setUp () {

			$this->setBroadcaster();

			$this->provideExceptionObjects();
		}

		private function setBroadcaster ():void {

			$stubs = [];

			if ($this->muffleExceptionBroadcast)

				$stubs["queueAlertAdapter"] = null;

			$this->exceptionBroadcaster = $this->positiveDouble($this->exceptionBroadcasterName, $stubs);
		}

		protected function provideExceptionObjects ():void {

			$this->massProvide([

				$this->exceptionBridge => $this->getExceptionBridge(),

				$this->exceptionBroadcasterName => $this->exceptionBroadcaster
			]);
		}

		protected function getExceptionBridge ():ModuleExceptionBridge {

			return $this->replaceConstructorArguments($this->exceptionBridge,

			[
				"exceptionDetector" => $this->exceptionBroadcaster
			],

			[
				"writeStatusCode" => null,

				"disgracefulShutdown" => $this->returnArgument(1),

				"gracefulShutdown" => $this->returnArgument(0)
			]);
		}

		protected function assertWillCatchPayload ($payload) {

			$sut = $this->mockCalls([

				$this->alerterMethod => [1, [

					$this->returnCallback(function ($subject) {

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

					$this->returnCallback(function ($subject) use ($exception) {

						return $subject instanceof $exception;
					}),
					$this->anything()
				]]
			], $this->exceptionBroadcaster);

			$this->massProvide([ $this->exceptionBroadcasterName => $sut ]);
		}
	}
?>