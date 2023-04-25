<?php

namespace Suphle\Contracts\Database;

use Suphle\Contracts\Auth\{AuthStorage, UserHydrator};

use Suphle\Contracts\Routing\OutputsCrudFiles;

interface OrmDialect extends OutputsCrudFiles
{
    public function getConnection(): object;

    public function runTransaction(callable $queries, array $lockModels = [], bool $hardLock = false);

    public function registerObservers(array $observers, AuthStorage $authStorage): void;

    /**
     * @return A builder/query object, with the filters applied
    */
    public function selectFields($builder, array $filters): object;

    public function applyLock(array $models, bool $isHard): void;

    /**
     * @return Modified [model]
    */
    public function addWhereClause($model, array $constraints);

    /**
     * The underlying vendor being wrapped by this adapter
    */
    public function getNativeClient(): object;

    /**
     * Lives here to guarantee user can only be hydrated when orm is ready
    */
    public function getUserHydrator(): UserHydrator;
}
