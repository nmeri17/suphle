<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Contracts\Database\{OrmReplicator, OrmDialect};

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use Tilwa\Adapters\Orms\Eloquent\Models\BaseModel;

	use Illuminate\Database\Migrations\Migrator;

	class ModelReplicator implements OrmReplicator {

		/**
		* @property BaseModel
		*/
		private $activeModel,

		$databaseConnection, $laravelContainer;

		public function __construct (OrmDialect $ormDialect, LaravelContainer $laravelContainer) {

			$this->databaseConnection = $ormDialect->getConnection();

			$this->laravelContainer = $laravelContainer;
		}

		public function seedDatabase ( int $amount):void {

			$this->activeModel::factory()->count($amount)->create();
		}

		public function getBeforeInsertion ( int $amount = 1, array $customizeFields = [], callable $customizeModel = null) {

			$builder = $this->activeModel::factory()->count($amount);

			$builder = !is_null($customizeModel) ? $customizeModel($builder): $builder;

			if (!empty($customizeFields))

				return $builder->make($customizeFields);

			return $builder->make();
		}

		public function getRandomEntity () {

			return $this->activeModel->inRandomOrder()->first();
		}

		public function getRandomEntities ( int $amount):array {

			return $this->activeModel->inRandomOrder()->limit($amount)->get();
		}

		public function setActiveModelType (string $model):void {

			$this->activeModel = new $model;
		}

		public function setupSchema ():void {

			$this->migrator = $migrator = $this->laravelContainer->make("migrator"); // bound to Migrator

			$repository = $migrator->getRepository();

			if (!$repository->repositoryExists())

				$repository->createRepository();

			// var_dump($migrator->run($this->activeModel::migrationFolders()), $this->activeModel::migrationFolders());
		}

		public function dismantleSchema ():void {

			$this->migrator->rollback($this->activeModel::migrationFolders());
		}

		public function listenForQueries ():void {

			$this->databaseConnection->beginTransaction();
		}

		public function stopQueryListen ():void {

			$this->databaseConnection->rollBack(); // Nothing to do here, since none of the queries were committed. But may be needed by another vendor
		}
	}
?>