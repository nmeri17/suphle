<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Contracts\Queues\Adapter;

	class StubbedQueueAdapter implements Adapter {

		private $pushedTasks = [];

		public function pushAction (string $taskClass, array $payload):void {

			$this->pushedTasks[$taskClass] = $payload;
		}

		public function processTasks ():void {

			//
		}

		public function configureNative ():void {

			//
		}

		public function didPushTask (string $class):bool {

			return array_key_exists($class, $this->pushedTasks);
		}
	}