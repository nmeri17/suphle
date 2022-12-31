<?php
	namespace Suphle\Exception\Jobs;

	use Suphle\Contracts\{Queues\Task, Exception\AlertAdapter};

	use Throwable;

	class DeferExceptionAlert implements Task {

		public function __construct(protected readonly Throwable $explosive, protected readonly AlertAdapter $alerter, private $activePayload) {

			//
		}

		public function handle ():void {

			$this->alerter->broadcastException($this->explosive, $this->activePayload);
		}
	}
?>