<?php
	namespace Tilwa\Adapters\Queues;

	use Qutee\{Task, Queue, Worker, Persistor\Redis};

	use Throwable;

	class QuTee extends BaseQueueAdapter {

		public function pushAction (string $taskClass, array $payload):void {

			Task::create($taskClass, $payload, Task::PRIORITY_HIGH);
		}

		public function processTasks ():void {

			$worker = new Worker;

			while (true) {
				try {
					
					$worker->run();
				}
				catch (Throwable $e) {
					
					echo $e->getMessage();
				}
			}
		}

		public function configureNative ():void {

			$queuePersistor = new Redis;

			$queuePersistor->setOptions([

				"host"  => "127.0.0.1", "port"  => 6379
			]);

			$this->client = new Queue;

			$this->client->setPersistor($queuePersistor);
		}
	}
?>