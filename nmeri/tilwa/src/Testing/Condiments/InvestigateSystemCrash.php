<?php
	namespace Tilwa\Testing\Condiments;

	use Throwable;

	/**
	 * Expects consuming class to already GagsException
	*/
	trait InvestigateSystemCrash {

		private $alerterMethod = "queueAlertAdapter";

		protected function assertWillCatchPayload ($payload):void {

			$this->mockCalls([

				/*$this->alerterMethod => [1, [

					$this->callback(function ($subject) {

						return in_array(Throwable::class, class_implements($subject));
					}),
					$this->equalTo($payload)
				]]*/
			], $this->exceptionBroadcaster);
var_dump(spl_object_hash($this->exceptionBroadcaster), 26); die; // manufacture a new broadcaster
			$this->provideExceptionBridge($this->exceptionBridgeStubs());
		}

		protected function exceptionBridgeStubs ():array {

			return [
				
				"disgracefulShutdown" => $this->getDisgracefulShutdown() // if we eventually have the need to test [disgracefulShutdown], insert this array from the invoked methods, according to requirements instead of this generic array
			];
		}

		protected function assertWillCatchException (string $exception):void {

			$this->mockCalls([

				$this->alerterMethod => [1, [

					$this->callback(function ($subject) use ($exception) {

						return $subject instanceof $exception;
					}),
					$this->anything()
				]]
			], $this->exceptionBroadcaster);

			$this->provideExceptionBridge($this->exceptionBridgeStubs());
		}
	}
?>