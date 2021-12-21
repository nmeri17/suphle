<?php

	namespace Tilwa\Adapters\Orms;

	use Tilwa\Contracts\{Orm, \Config\Orm as OrmConfig, LaravelApp};

	use Tilwa\Hydration\Container;

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

		public function factoryProduce ($model, $amount):void {

			$model->factory() // assumes each model points to its factory

			->count($amount)->create();
		}

		public function factoryLine ($model, int $amount, array $customAttributes) {

			$builder = $model->factory()->count($amount);

			if (!empty($customAttributes))

				return $builder->make($customAttributes);

			return $builder->make();
		}

		public function findAny ($model) {

			return $model->inRandomOrder()->first();
		}

		public function findAnyMany ($model, int $amount):array {

			return $model->inRandomOrder()->limit($amount)->get();
		}
	}
?>