<?php
	namespace Tilwa\Adapters\Queues;

	use Tilwa\Contracts\{Queues\Adapter, IO\EnvAccessor};

	use Resque as ResqueLib;

	class Resque implements Adapter {

		private $envAccessor;

		public function __construct (EnvAccessor $envAccessor) {

			$this->envAccessor = $envAccessor;
		}

		public function pushAction (string $taskClass, array $payload):void {

			ResqueLib::enqueue("task_queue", $taskClass, $payload);
		}

		public function processTasks ():void {

			$config = [
				"QUEUE" => "*", // all queues

				"COUNT" => 5, // number of forks to spawn

				// "PREFIX" => "task_queue"
			];

			foreach ($config as $name => $value)

				$this->envAccessor->setField($name, $value);

			include(
				$this->envAccessor->getField("COMPOSER_RUNTIME_BIN_DIR") // composer sets this for us
				. "resque"
			);
		}

		public function configureNative ():void {

			ResqueLib::setBackend("localhost:6379");
		}
	}
?>