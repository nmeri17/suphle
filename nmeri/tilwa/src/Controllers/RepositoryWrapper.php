<?php
	namespace Tilwa\Controllers;

	use Tilwa\Contracts\ReboundsEvents;

	use Tilwa\Errors\UnauthorizedServiceAccess;

	class RepositoryWrapper extends ServiceWrapper {

		protected function yield(string $method, array $arguments) {

			$service = $this->activeService;

			$serviceName = $service::class;

			$result = parent::yield($method, $arguments);

			if ($service instanceof QueryService && !$service->shouldFetch($result))

				throw new UnauthorizedServiceAccess($serviceName);

			if ($service instanceof ReboundsEvents)

				$this->eventManager->emit($serviceName, "refresh", compact("result", "method"));

			return $result;
		}
	}
?>