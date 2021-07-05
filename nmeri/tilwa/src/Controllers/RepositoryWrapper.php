<?php
	namespace Tilwa\Controllers;

	use Tilwa\Contracts\{ReboundsEvents, Orm, ReboundsEvents, CommandService};

	use Tilwa\Controllers\Structures\ServiceEventPayload;

	class RepositoryWrapper extends ServiceWrapper {

		protected $orm;

		public function __construct (EventManager $eventManager, Services $config, Orm $orm) {
			
			parent::__construct($eventManager, $config);

			$this->orm = $orm;
		}

		protected function yield(string $method, array $arguments) {

			$service = $this->activeService;

			$result = $this->getResult($service, $method, $arguments);

			if ($service instanceof ReboundsEvents)

				$this->eventManager->emit(get_class($service), "refresh", new ServiceEventPayload($result, $method));

			return $result;
		}

		private function getResult( $service, string $method, $arguments) {

			if ($service instanceof CommandService)

				return $this->orm->runTransaction(function () use ($method, $arguments) {

					parent::yield($method, $arguments);
				});

			return parent::yield($method, $arguments);
		}
	}
?>