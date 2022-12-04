<?php
	namespace Suphle\Exception\Jobs;

	use Suphle\Contracts\{Queues\Task, Exception\AlertAdapter};

	use Throwable;

	class DeferExceptionAlert implements Task {

		public function __construct(private readonly Throwable $explosive, private readonly AlertAdapter $alerter, private $activePayload)
  {
  }

		public function handle ():void {

			$this->alerter->broadcastException($this->explosive, $this->activePayload);
		}
	}
?>