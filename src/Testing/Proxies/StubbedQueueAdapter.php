<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Adapters\Queues\BaseQueueAdapter;

	class StubbedQueueAdapter extends BaseQueueAdapter {

		private array $pushedTasks = [];

		public function pushAction (string $taskClass, array $payload):void {

			$this->pushedTasks[$taskClass] = $payload;
		}

		public function processTasks ():void {

			foreach ($this->pushedTasks as $taskClass => $payload)

				(new $taskClass(...array_values($payload)))

				->handle();
		}

		public function configureNative ():void {

			//
		}

		public function didPushTask (string $class):bool {

			return array_key_exists($class, $this->pushedTasks);
		}
	}