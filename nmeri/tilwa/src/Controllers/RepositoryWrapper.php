<?php
	namespace Tilwa\Controllers;

	use Tilwa\Contracts\{ Database\Orm, Services\CommandService};

	class RepositoryWrapper extends ServiceWrapper {

		protected $orm;

		public function __construct (EventManager $eventManager, Services $config, Orm $orm) {
			
			parent::__construct($eventManager, $config);

			$this->orm = $orm;
		}

		protected function yield(string $method, array $arguments) {

			return $this->getResult($this->activeService, $method, $arguments);
		}

		private function getResult( $service, string $method, $arguments) {

			if ($service instanceof CommandService) // NOTE: we now have alternate way of detecting who this belongs to

				return $this->orm->runTransaction(function () use ($method, $arguments) { // you didn't lock before transacting

					parent::yield($method, $arguments);
				});

			return parent::yield($method, $arguments);
		}
	}
?>