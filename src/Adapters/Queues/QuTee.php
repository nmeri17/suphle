<?php
	namespace Suphle\Adapters\Queues;

	use Suphle\Contracts\IO\EnvAccessor;

	use Qutee\{Task, Queue, Worker, Persistor\Redis};

	use Throwable;

	class QuTee extends BaseQueueAdapter {

		public function __construct(private readonly EnvAccessor $envAccessor)
  {
  }

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

				"host"  => $this->envAccessor->getField("REDIS_HOST"),

				"port"  => $this->envAccessor->getField("REDIS_PORT")
			]);

			$this->client = new Queue;

			$this->client->setPersistor($queuePersistor);
		}
	}
?>