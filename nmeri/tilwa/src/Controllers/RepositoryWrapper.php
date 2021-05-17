<?php
	namespace Tilwa\Controllers;

	use Tilwa\Contracts\{ReboundsEvents, Orm};

	use Tilwa\Errors\UnauthorizedServiceAccess;

	use Tilwa\Controllers\Structures\ServiceEventPayload;

	class RepositoryWrapper extends ServiceWrapper {

		protected $orm;

		public function __construct (EventManager $eventManager, Services $config, Orm $orm) {
			
			parent::__construct($eventManager, $config);

			$this->orm = $orm;
		}

		protected function yield(string $method, array $arguments) {

			$service = $this->activeService;

			$serviceName = $service::class;

			$result = $this->getResult($service, $method, $arguments);

			if ($service instanceof QueryService && !$service->shouldFetch($result))

				throw new UnauthorizedServiceAccess($serviceName);

			if ($service instanceof ReboundsEvents)

				$this->eventManager->emit($serviceName, "refresh", new ServiceEventPayload($result, $method));

			return $result;
		}

		private function getResult(object $service, string $method, $arguments) {

			if ($service instanceof CommandService)

				return $this->orm->runTransaction(function () use ($method, $arguments) {

					parent::yield($method, $arguments);
				});

			return parent::yield($method, $arguments);
		}
	}
?>