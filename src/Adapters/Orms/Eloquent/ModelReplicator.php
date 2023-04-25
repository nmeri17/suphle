<?php

namespace Suphle\Adapters\Orms\Eloquent;

use Suphle\Hydration\Container;

use Suphle\Contracts\Database\{OrmReplicator, OrmDialect};

use Suphle\Contracts\{Config\Database, Bridge\LaravelContainer};

use Suphle\Adapters\Orms\Eloquent\Models\BaseModel;

use Illuminate\Database\{Connection, Migrations\Migrator};

use Exception;
use PDO;

class ModelReplicator implements OrmReplicator
{
    protected BaseModel $activeModel;

    protected Connection $databaseConnection;

    protected Migrator $migrator;

    protected array $credentials = [];

    public function __construct(
        protected readonly Container $container,
        OrmDialect $ormDialect,
        LaravelContainer $laravelContainer,
        Database $config
    ) {

        $this->credentials = $config->getCredentials();

        $this->databaseConnection = $ormDialect->getConnection();

        $this->migrator = $laravelContainer->make("migrator");
    }

    public function seedDatabase(int $amount): void
    {

        $this->activeModel::factory()->count($amount)->create();
    }

    public function getCount(): int
    {

        return $this->activeModel->count();
    }

    /**
     * @return Collection
    */
    public function modifyInsertion(int $amount = 1, array $customizeFields = [], callable $customizeModel = null): iterable
    {

        $builder = $this->activeModel::factory()->count($amount);

        $builder = !is_null($customizeModel) ? $customizeModel($builder) : $builder;

        if (!empty($customizeFields)) {

            return $builder->create($customizeFields);
        }

        return $builder->create();
    }

    public function getRandomEntity(array $relations = []): object
    {

        return $this->activeModel->inRandomOrder()->with($relations)

        ->first();
    }

    public function getRandomEntities(
        int $amount,
        array $relations = []
    ): iterable {

        return $this->activeModel->inRandomOrder()->limit($amount)

        ->with($relations)->get();
    }

    public function getSpecificEntities(
        int $amount,
        array $constraints,
        array $relations = []
    ): iterable {

        return $this->activeModel->limit($amount)->with($relations)

        ->where($constraints)->get();
    }

    public function setActiveModelType(string $model): void
    {

        $this->activeModel = $this->container->getClass($model); // resolving from container so it can be stubbed out
    }

    public function setupSchema(): void
    {

        $this->mayCreateDatabase();

        $repository = $this->migrator->getRepository();

        if (!$repository->repositoryExists()) {

            $repository->createRepository();
        }

        $this->validateMigrationPaths();

        $this->migrator->run($this->activeModel::migrationFolders());
    }

    private function validateMigrationPaths(): void
    {

        $model = $this->activeModel;

        foreach ($model::migrationFolders() as $path) {

            if (!is_dir($path)) {

                throw new Exception("Invalid migration path given while trying to migrate model ". $model::class. ": $path");
            }
        }
    }

    public function dismantleSchema(): void
    {

        if (!getenv("SUPHLE_NUKE_DB")) {
            return;
        }

        $this->migrator->rollback($this->activeModel::migrationFolders());

        $this->dropDatabase();
    }

    public function listenForQueries(): void
    {

        $this->databaseConnection->beginTransaction();
    }

    public function revertHeardQueries(): void
    {

        $this->databaseConnection->rollBack();
    }

    protected function getPdoConnection(array $connectionDetails): PDO
    {

        extract($connectionDetails);

        $instance = new PDO("$driver:host=$host", $username, $password);

        $instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $instance;
    }

    protected function mayCreateDatabase(): void
    {

        foreach ($this->credentials as $connectionDetails) {

            $sanitizedName = "`".

                str_replace("`", "``", $connectionDetails["database"]).

                "`";

            $connection = $this->getPdoConnection($connectionDetails);

            $connection->query("CREATE DATABASE IF NOT EXISTS $sanitizedName");

            $connection->query("use $sanitizedName");
        }
    }

    protected function dropDatabase(): void
    {

        foreach ($this->credentials as $connectionDetails) {

            $connection = $this->getPdoConnection($connectionDetails);

            $connection->exec("DROP database ". $connectionDetails["database"]);
        }
    }
}
