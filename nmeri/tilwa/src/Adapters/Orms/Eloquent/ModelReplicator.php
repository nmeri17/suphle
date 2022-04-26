<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Contracts\Database\{OrmReplicator, OrmDialect};

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use Tilwa\Adapters\Orms\Eloquent\Models\BaseModel;

	use Illuminate\Database\Migrations\Migrator;

	use Exception;

	class ModelReplicator implements OrmReplicator {

		/**
		* @property BaseModel
		*/
		private $activeModel,

		$databaseConnection, $laravelContainer, $migrator;

		public function __construct (OrmDialect $ormDialect, LaravelContainer $laravelContainer) {

			$this->databaseConnection = $ormDialect->getConnection();

			$this->laravelContainer = $laravelContainer;

			$this->migrator = $laravelContainer->make("migrator"); // bound to Migrator
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

		public function getBeforeInsertion ( int $amount = 1, array $customizeFields = [], callable $customizeModel = null) {

			$builder = $this->activeModel::factory()->count($amount);

			$builder = !is_null($customizeModel) ? $customizeModel($builder): $builder;

			if (!empty($customizeFields))

				return $builder->make($customizeFields);

			return $builder->make();
		}

		public function getRandomEntity ():object {

			return $this->activeModel->inRandomOrder()->first();
		}

		public function getRandomEntities ( int $amount):iterable {

			return $this->activeModel->inRandomOrder()->limit($amount)->get();
		}

		public function setActiveModelType (string $model):void {

			$this->activeModel = new $model;
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