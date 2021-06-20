<?php

	namespace Tilwa\Adapters\Orms;

	use Tilwa\Contracts\{Orm, \Config\Orm as OrmConfig, LaravelApp};

	use Tilwa\App\Container;

	use PDO;

	class Eloquent implements Orm {

		private $credentials, $connection, $laravelContainer,

		$authStorage;

		public function __construct (OrmConfig $config, Container $container, LaravelApp $laravelContainer, AuthStorage $authStorage) {

			$this->credentials = $config->getCredentials();

			$this->laravelContainer = $laravelContainer;

			$this->container = $container;

			$this->authStorage = $authStorage;
		}

		private function setConnection():void {

			// do stuff with that capsule whatever. ensure the container given to it is [laravelContainer]
		}

		public function getConnection () {

			if (is_null($this->connection)) $this->setConnection();

			return $this->connection;
		}

		public function setTrap(callable $callback) {

			//
		}

		public function getPaginationPath():string {

			return "next_page_url";
		}

		public function runTransaction(callable $queries):void {

			//
		}

		public function registerObservers(array $observers):void {

			foreach ($observers as $model => $observer) {

				$concrete = $this->container->getClass($model);

				$concrete::observe($observer);
			}

			if (!empty($observers))

				$this->laravelContainer->bind(AuthStorage::class, function () {

					return $this->authStorage; // guards in those observers will be relying on this value
				});
		}
	}
?>