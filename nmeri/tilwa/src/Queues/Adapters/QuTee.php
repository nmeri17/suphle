<?php
	namespace Tilwa\Queues\Adapters;

	use Tilwa\Contracts\Queues\Adapter;

	use Qutee\{Task, Queue, Worker, Persistor\Redis};

	use Exception;

	class QuTee implements Adapter {

		public function pushAction (string $taskClass, array $payload):void {

			Task::create($taskClass, $payload, Task::PRIORITY_HIGH);
		}

		public function processTasks ():void {

			$worker = new Worker;

			while (true) {
				try {
					
					$worker->run();
				}
				catch (Exception $e) {
					
					echo $e->getMessage();
				}
			}
		}

		public function configureNative ():void {

			$queuePersistor = new Redis;

			$queuePersistor->setOptions([

				"host"  => "127.0.0.1", "port"  => 6379
			]);

			(new Queue)->setPersistor($queuePersistor);
		}
	}
?>