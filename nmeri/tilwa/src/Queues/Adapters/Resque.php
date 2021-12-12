<?php
	namespace Tilwa\Queues\Adapters;

	use Tilwa\Contracts\Queues\Adapter;

	use Resque as ResqueLib;

	class Resque implements Adapter {

		public function pushAction (string $taskClass, array $payload):void {

			ResqueLib::enqueue("task_queue", $taskClass, $payload);
		}

		public function processTasks ():void {

			$config = [
				"QUEUE" => "*", // all queues

				"COUNT" => 5, // number fo forks to spawn

				// "PREFIX" => "task_queue"
			];

			foreach ($config as $name => $value)

				setenv($name, $value);

			include("bin/resque");
		}

		public function configureNative ():void { // should be called in app boot? :o for each request? :oo

			ResqueLib::setBackend("localhost:6379");
		}
	}
?>