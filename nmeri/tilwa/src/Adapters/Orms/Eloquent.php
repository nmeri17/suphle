<?php
	namespace Tilwa\Adapters\Orms;

	use Tilwa\Contracts\{Database\Orm, Config\Database, Bridge\LaravelContainer};

	use Tilwa\Hydration\Container;

	use PDO;

	class Eloquent implements Orm {

		private $credentials, $connection, $laravelContainer,

		$authStorage;

		public function __construct (Database $config, Container $container, LaravelContainer $laravelContainer, AuthStorage $authStorage) {

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

		public function runTransaction(callable $queries):void {

			// obtain lock before running
		}

		public function registerObservers(array $observers):void {

			if (!empty($observers)) {

				$this->laravelContainer->bind(AuthStorage::class, function () {

					return $this->authStorage; // guards in those observers will be relying on this value
				});

				foreach ($observers as $model => $observer)

					$this->container->getClass($model)::observe($observer);
			}
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

		public function saveOne ($model):void {

			$model->save();
		}
	}
?>