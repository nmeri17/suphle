<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Hydration\Container;

	use Tilwa\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Tilwa\Contracts\{Database\OrmDialect, Config\Database, Bridge\LaravelContainer};

	use Tilwa\Contracts\Auth\{UserHydrator as HydratorContract, AuthStorage, UserContract};

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
		public function registerObservers (array $observers, AuthStorage $authStorage):void {

			if (empty($observers)) return;

			$this->laravelContainer->instance(AuthStorage::class, $authStorage); // guards in those observers will be relying on this contract

			foreach ($observers as $model => $observer)

				$model::observe($this->container->getClass($observer));
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

		public function getUserHydrator ():HydratorContract {

			$hydrator = $this->container->getClass(UserHydrator::class);

			$hydrator->setUserModel($this->userModel());

			return $hydrator;
		}

		public function userModel ():UserContract {

			return $this->container->getClass(EloquentUser::class);
		}
	}
?>