<?php

	namespace Tilwa\Controllers;

	use Tilwa\Contracts\Orm;

	// this only wraps InterceptsQuery. AlterCommands are handled by QueryEmitter
	class RepositoryWrapper extends ServiceWrapper {

		protected function yield(string $method, array $arguments) {

			$service = $this->activeService;

			$serviceName = $service::class;

			$service->ormListener($this->onCatch($serviceName, $method));

			$result = $service->$method(...$arguments);
				
			if (!$service->shouldFetch($result))

				throw new UnauthorizedServiceAccess($serviceName);

			return $result;
		}

		private function onCatch(string $serviceName, string $method) {
			
			return function($bindings) use ($method, $serviceName) {

				if (empty($bindings)) // $query->bindings

					throw new InvalidRepositoryMethod($method);
				
				if ($this->lifecycle)

					$this->eventManager->emit($serviceName, "fetched", compact("bindings"));
			};
		}
	}
?>