<?php
	namespace Tilwa\Queues;

	use Tilwa\Hydration\Container;

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

		public function augmentArguments (string $taskClass, array $deferredDependencies):void {

			$parameters = $this->container->whenType($taskClass)->needsArguments($deferredDependencies)

			->getMethodParameters(Container::CLASS_CONSTRUCTOR, $taskClass);

			$this->addTask($taskClass, $parameters);
		}

	}
?>