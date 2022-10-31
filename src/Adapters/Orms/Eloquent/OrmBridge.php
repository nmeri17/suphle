<?php
	namespace Suphle\Adapters\Orms\Eloquent;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\{Database\OrmDialect, Config\Database, Bridge\LaravelContainer, Services\Decorators\BindsAsSingleton};

	use Suphle\Contracts\Auth\{UserHydrator as HydratorContract, AuthStorage, UserContract};

	use Illuminate\Database\{DatabaseManager, Capsule\Manager as CapsuleManager, Connection};

	class OrmBridge implements OrmDialect, BindsAsSingleton {

		private $credentials, $connection, $laravelContainer,

		$nativeClient, $container;

		public function __construct (Database $config, Container $container, LaravelContainer $laravelContainer) {

			$this->credentials = $config->getCredentials();

			$this->laravelContainer = $laravelContainer;

			$this->container = $container;
		}

		public function entityIdentity ():string {

			return OrmDialect::class;
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

				$this->applyLock($lockModels, $hardLock);

				return $queries();
			});
		}

		public function applyLock(array $models, bool $isHard):void {

			$firstModel = current($models);

			$modelName = $firstModel::class;

			$primaryField = $firstModel->getKeyName();

			$lockingMethod = $isHard ? "lockForUpdate": "sharedLock";

			(new $modelName)->$lockingMethod()->whereIn( // combine user query state into special locking builder to avoid applying update to all rows

				$primaryField, array_map(fn($model) => $model->$primaryField, $models)
			)->get();
		}

		/**
		 * {@inheritdoc}
		*/
		public function registerObservers (array $observers, AuthStorage $authStorage):void {

			if (empty($observers)) return;

			$this->laravelContainer->instance(AuthStorage::class, $authStorage); // guards in those observers will be relying on this contract

			foreach ($observers as $model => $observer) {

				$this->laravelContainer->bind($observer, function ($app) use ($observer) {

					return $this->container->getClass($observer); // just to be on the safe side in case observer has bound entities
				});

				$model::observe($observer); // even if we hydrate an instance, they'll still flatten it, anyway
			}
		}

		public function selectFields ($builder, array $filters):object {

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

			$hydrator->setUserModel(

				$this->container->getClass(UserContract::class)
			);

			return $hydrator;
		}

		/**
		 * {@inheritdoc}
		*/
		public function restoreConnections (array $modules):void {

			foreach ($modules as $descriptor)

				$descriptor->getContainer()->getClass(OrmDialect::class);
		}
	}
?>