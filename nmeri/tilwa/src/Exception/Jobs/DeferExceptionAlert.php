<?php
	namespace Tilwa\Exception\Jobs;

	use Tilwa\Contracts\{Queues\Task, Exception\AlertAdapter};

	use Throwable;

	class DeferExceptionAlert implements Task {

		private $explosive, $alerter;

		public function __construct (Throwable $explosive, AlertAdapter $alerter) {

			$this->explosive = $explosive;

			$this->alerter = $alerter;
		}

		public function handle ():void {

			$this->alerter->broadcastException($this->explosive);
		}
	}
?>