<?php
	namespace Suphle\Adapters\Orms\Eloquent;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Database\{OrmReplicator, OrmDialect};

	use Suphle\Contracts\Bridge\LaravelContainer;

	use Suphle\Adapters\Orms\Eloquent\Models\BaseModel;

	use Illuminate\Database\Migrations\Migrator;

	use Exception;

	class ModelReplicator implements OrmReplicator {

		/**
		* @property BaseModel
		*/
		private $activeModel,

		$databaseConnection, $laravelContainer, $migrator, $container;

		public function __construct (OrmDialect $ormDialect, LaravelContainer $laravelContainer, Container $container) {

			$this->databaseConnection = $ormDialect->getConnection();

			$this->laravelContainer = $laravelContainer;

			$this->migrator = $laravelContainer->make("migrator"); // bound to Migrator

			$this->container = $container;
		}

		public function seedDatabase ( int $amount):void {

			$this->activeModel::factory()->count($amount)->create();
		}

		public function stopQueryListen ():void {

			$this->databaseConnection->rollBack();
		}

		public function getCount ():int {

			return $this->activeModel->count();
		}

		/**
		 * @return Collection
		*/
		public function modifyInsertion ( int $amount = 1, array $customizeFields = [], callable $customizeModel = null):iterable {

			$builder = $this->activeModel::factory()->count($amount);

			$builder = !is_null($customizeModel) ? $customizeModel($builder): $builder;

			if (!empty($customizeFields))

				return $builder->create($customizeFields);

			return $builder->create();
		}

		public function getRandomEntity ():object {

			return $this->activeModel->inRandomOrder()->first();
		}

		public function getRandomEntities ( int $amount):iterable {

			return $this->activeModel->inRandomOrder()->limit($amount)->get();
		}

		public function getSpecificEntities ( int $amount, array $constraints):iterable {

			return $this->activeModel->limit($amount)->where($constraints)->get();
		}

		public function setActiveModelType (string $model):void {

			$this->activeModel = $this->container->getClass($model); // resolving from container so it can be stubbed out
		}

		public function setupSchema ():void {

			$repository = $this->migrator->getRepository();

			if (!$repository->repositoryExists())

				$repository->createRepository();

			$this->validateMigrationPaths();

			$this->migrator->run($this->activeModel::migrationFolders());
		}

		private function validateMigrationPaths ():void {

			$model = $this->activeModel;

			foreach ($model::migrationFolders() as $path)

				if (!is_dir($path))

					throw new Exception("Invalid migration path given while trying to migrate model ". get_class($model). ": $path");
		}

		public function dismantleSchema ():void {

			$this->migrator->rollback($this->activeModel::migrationFolders());
		}

		public function listenForQueries ():void {

			$this->databaseConnection->beginTransaction();
		}
	}
?>