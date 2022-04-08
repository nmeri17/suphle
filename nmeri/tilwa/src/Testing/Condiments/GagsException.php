<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Testing\Condiments\MockFacilitator;

	use Throwable;

	trait GagsException {

		use MockFacilitator;

		private $exceptionManager,

		$alerterMethod = "queueAlertAdapter",

		$managerName = DetectedExceptionManager::class;

		protected function setUp () {

			$this->exceptionManager = $this->negativeDouble($this->managerName, []);
		}

		protected function assertCaughtPayload ($payload) {

			$sut = $this->mockCalls([

				$this->alerterMethod => [1, [

					$this->returnCallback(function ($subject) {

						return $subject instanceof Throwable;
					}),
					$this->equalTo($payload)
				]]
			], $this->exceptionManager);

			$this->massProvide([

				$this->managerName => $sut
			]);
		}

		protected function assertCaughtException (string $exception) {

			$sut = $this->mockCalls([

				$this->alerterMethod => [1, [

					$this->returnCallback(function ($subject) use ($exception) {

						return $subject instanceof $exception;
					}),
					$this->anything()
				]]
			], $this->exceptionManager);

			$this->massProvide([ $this->managerName => $sut ]);
		}

		abstract protected function massProvide (array $provisions):void;
	}
?>