<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Adapters\Queues\BaseQueueAdapter;

	class StubbedQueueAdapter extends BaseQueueAdapter {

		private array $pushedTasks = [], $executedTasks = [];

		private bool $executeOnPush = false;

		public function pushAction (string $taskClass, array $payload):void {

			$this->pushedTasks[$taskClass] = $payload;

			if ($this->executeOnPush) $this->processTasks();
		}

		public function setExecuteOnPush ():void {

			$this->executeOnPush = true;
		}

		public function processTasks ():void {

			foreach ($this->pushedTasks as $taskClass => $payload) {

				$this->hydrateTask($taskClass, $payload)->handle();

				$this->addExecutedTask($taskClass, $payload);
			}
		}

		protected function addExecutedTask (string $taskClass, array $payload):void {

			unset($this->pushedTasks[$taskClass]);

			$this->executedTasks[$taskClass] = $payload;
		}

		public function configureNative ():void {

			//
		}

		/**
		 * Only reports tasks pending execution
		*/
		public function didPushTask (string $class):bool {

			return array_key_exists($class, $this->pushedTasks);
		}
	}