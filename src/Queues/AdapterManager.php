<?php
	namespace Suphle\Queues;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Queues\Adapter;

	class AdapterManager {

		public function __construct (
			private readonly Adapter $activeAdapter, 

			private readonly Container $container
		) {

			//
		}

		public function addTask (string $taskClass, array $payload):void {

			$this->activeAdapter->pushAction($taskClass, $payload);
		}

		public function augmentArguments (string $taskClass, array $deferredDependencies):void {

			$parameters = $this->container->whenType($taskClass)

			->needsArguments($deferredDependencies)

			->getMethodParameters(Container::CLASS_CONSTRUCTOR, $taskClass);

			$this->addTask($taskClass, $parameters);
		}

	}
?>