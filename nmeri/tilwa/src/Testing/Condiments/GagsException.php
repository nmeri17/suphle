<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Testing\Condiments\MockFacilitator;

	use Throwable;

	trait GagsException {

		use MockFacilitator;

		private $exceptionManager,

		$alerterMethod = "queueAlertAdapter",

		$managerName = DetectedExceptionManager::class;;

		protected function setUp () {

			$this->exceptionManager = $this->negativeStub($this->managerName, []);
		}

		protected function assertCaughtPayload ($payload) {

			$this->exceptionManager->expects($this->once())

			->method($this->alerterMethod)
			
			->with( $this->callback(function ($subject) {

				return $subject instanceof Throwable;
			}), $this->equalTo($payload) );

			$this->massProvide([

				$this->managerName => $this->exceptionManager
			]);
		}

		protected function assertCaughtException (string $exception) {

			$this->exceptionManager->expects($this->once())

			->method($this->alerterMethod)
			
			->with( $this->callback(function ($subject) use ($exception) {

				return $subject instanceof $exception;
			}), $this->anything() );

			$this->massProvide([

				$this->managerName => $this->exceptionManager
			]);
		}

		abstract protected function massProvide ():void;
	}
?>