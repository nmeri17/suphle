<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Contracts\{Database\OrmDialect, Config\Database, Bridge\LaravelContainer};

	use Tilwa\Hydration\Container;

	use Illuminate\Database\{DatabaseManager, Capsule\Manager as CapsuleManager};

	class OrmBridge implements OrmDialect {

		private $credentials, $connection, $laravelContainer,

		$authStorage;

		public function __construct (Database $config, Container $container, LaravelContainer $laravelContainer, AuthStorage $authStorage) {

			$this->credentials = $config->getCredentials();

			$this->laravelContainer = $laravelContainer;

			$this->container = $container;

			$this->authStorage = $authStorage;
		}

		/**
		 * @param {drivers} Assoc array with structure [credentials => [], name => ?string]
		*/
		public function setConnection (array $drivers = []):void {

			$capsule = new CapsuleManager;

			if (empty($drivers))

				$connections = [
					"credentials" => $this->credentials,

					"name" => "mysql"
				];

			else $connections = $drivers;

			foreach ($connections as $driver)

				$capsule->addConnection($driver["credentials"], @$driver["name"]);

			$this->connection = $capsule;
		}

		public function getConnection ():CapsuleManager {

			if (is_null($this->connection)) $this->setConnection();

			return $this->connection;
		}

		/**
		 *	Obtain lock before running
		*/
		public function runTransaction(callable $queries, array $lockModels = [], bool $hardLock = false) {

			return $this->connection->transaction(function () use ($lockModels, $hardLock, $queries) { // under the hood, capsule forwards the [transaction] call to [DatabaseManager], which it bases most operations on

				foreach ($lockModels as $model)

					if ($hardLock) $this->hardLock($model);

					else $this->softLock($model);

				return $queries();
			});
		}

		public function hardLock( $model):void {

			$model->lockForUpdate()->get();
		}

		public function softLock( $model):void {

			$model->sharedLock()->get();
		}

		/**
		 * {@inheritdoc}
		*/
		public function registerObservers(array $observers):void {

			if (empty($observers)) return;

			$this->laravelContainer->bind(AuthStorage::class, function () {

				return $this->authStorage; // guards in those observers will be relying on this value
			});

			foreach ($observers as $model => $observer)

				$this->container->getClass($model)::observe($observer);
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

		public function selectFields ($builder, array $filters) {

			return $builder->select($filters);
		}

		public function addWhereClause( $model, array $constraints) {

			return $model->where($constraints);
		}
	}
?>