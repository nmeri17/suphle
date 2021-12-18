<?php
	namespace Tilwa\Queues;

	use Tilwa\App\Container;

	use Tilwa\Contracts\Queues\Adapter;

	class AdapterManager {

		private $adapter, $container;

		public function __construct (Adapter $activeAdapter, Container $container) {

			$this->adapter = $activeAdapter;

			$this->container = $container;
		}

		public function addTask (string $taskClass, array $payload):void {

			$this->adapter->pushAction($taskClass, $payload);
		}

		public function beginProcessing ():void {

			$this->adapter->configureNative();

			$this->adapter->processTasks();
		}

		public function augmentArguments (string $taskClass, array $deferredDependencies):void {

			$parameters = $this->container->getMethodParameters("__construct", $taskClass);

			$typedParameters = array_map("get_class", $parameters);

			foreach ($deferredDependencies as $override) {

				$index = array_search(get_class($override), $typedParameters);

				$parameters[$index] = $override;
			}

			$this->addTask($taskClass, $parameters);
		}

	}
?>