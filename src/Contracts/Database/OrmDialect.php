<?php
	namespace Suphle\Contracts\Database;

	use Suphle\Contracts\Auth\{AuthStorage, UserHydrator};

	interface OrmDialect {

		public function getConnection ():object;

		public function runTransaction(callable $queries, array $lockModels = [], bool $hardLock = false);

		public function registerObservers(array $observers, AuthStorage $authStorage):void;

		/**
		 * @return A builder/query object, with the filters applied
		*/
		public function selectFields ($builder, array $filters):object;

		public function applyLock(array $models, bool $isHard):void;

		/**
		 * @return Modified [model]
		*/
		public function addWhereClause( $model, array $constraints);

		/**
		 * The underlying vendor being wrapped by this adapter
		*/
		public function getNativeClient ():object;

		/**
		 * Lives here to guarantee user can only be hydrated when orm is ready
		*/
		public function getUserHydrator ():UserHydrator;

		/**
		 * Undo connection resetting action so underlying ORM client's subsequent calls aren't disrupted by trying to hydrate a blank connection instance
		 * 
		 * During request handling, path clears eloquent/laravel instance, connection is wiped. If we want to do additional work with that connection e.g. drop migration, it will be unavailable unless this method is used to revive a new one. Without it, we'll get the error: "Database connection [] not configured"
		 * 
		 * I don't know whether it's applicable to other ORMs aside Eloquent. Recommended use is when path missing in a module is sent via RequestDetails. This action will overwrite connection for that module and unable to further render any subsequent BaseDatabasePopulator operation
		*/
		public function restoreConnections (array $modules):void;
	}
?>