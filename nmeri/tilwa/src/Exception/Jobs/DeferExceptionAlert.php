<?php
	namespace Tilwa\Exception\Jobs;

	use Tilwa\Contracts\{Queues\Task, Exception\AlertAdapter};

	use Throwable;

	class DeferExceptionAlert implements Task {

		private $explosive, $alerter, $activePayload;

		public function __construct (Throwable $explosive, AlertAdapter $alerter, $activePayload) {

			$this->explosive = $explosive;

			$this->alerter = $alerter;

			$this->activePayload = $activePayload;
		}

		public function handle ():void {

			$this->alerter->broadcastException($this->explosive, $this->activePayload);
		}
	}
?>