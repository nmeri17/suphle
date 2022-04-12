<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Contracts\{Database\OrmDialect, Config\Database, Bridge\LaravelContainer, Auth\AuthStorage};

	use Tilwa\Hydration\Container;

	use Illuminate\Database\{DatabaseManager, Capsule\Manager as CapsuleManager, Connection};

	class OrmBridge implements OrmDialect {

		private $credentials, $connection, $laravelContainer,

		$nativeClient;

		public function __construct (Database $config, Container $container, LaravelContainer $laravelContainer) {

			$this->credentials = $config->getCredentials();

			$this->laravelContainer = $laravelContainer;

			$this->container = $container;
		}

		/**
		 * @param {drivers} Assoc array with shape [name => [username, password, driver, database]]
		*/
		public function setConnection (array $drivers = []):void {

			$nativeClient = $this->nativeClient = new CapsuleManager;

			if (empty($drivers)) $connections = $this->credentials;

			else $connections = $drivers;

			foreach ($connections as $name => $credentials)

				$nativeClient->addConnection($credentials, $name);

			$this->connection = $nativeClient->getConnection();
		}

		/**
		 * @return Connection
		*/
		public function getConnection ():object {

			if (is_null($this->connection)) $this->setConnection();

			return $this->connection;
		}

		/**
		 *	Obtain lock before running
		*/
		public function runTransaction(callable $queries, array $lockModels = [], bool $hardLock = false) {

			return $this->connection->transaction(function () use ($lockModels, $hardLock, $queries) {

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

			$authStorageName = AuthStorage::class;

			$authStorage = $this->container->getClass($authStorageName); // using local rather than instance property for this so it doesn't impede userEntity/model/authStorage from hydrating due to interface concrete circular dependencies

			$this->laravelContainer->instance($authStorageName, $authStorage); // guards in those observers will be relying on this value

			foreach ($observers as $model => $observer) {

				$observerName = $model. "Authorization";

				$this->laravelContainer->instance($observerName, $this->container->getClass($observer)); // this works since this is the same container passed to the eventDispatcher for use in hydrating the listeners

				$model::observe($observerName);
			}
		}

		public function selectFields ($builder, array $filters) {

			return $builder->select($filters);
		}

		public function addWhereClause( $model, array $constraints) {

			return $model->where($constraints);
		}

		public function getNativeClient ():object {

			return $this->nativeClient;
		}
	}
?>