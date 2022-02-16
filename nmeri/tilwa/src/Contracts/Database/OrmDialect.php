<?php
	namespace Tilwa\Contracts\Database;

	interface OrmDialect {

		public function getConnection ():object;

		public function runTransaction(callable $queries, array $lockModels = [], bool $hardLock = false);

		public function registerObservers(array $observers):void;

		/**
		 * @return A builder/query object, with the filters applied
		*/
		public function selectFields ($builder, array $filters);

		public function hardLock( $model):void;

		public function softLock( $model):void;

		/**
		 * @return Modified [model]
		*/
		public function addWhereClause( $model, array $constraints);

		/**
		 * The underlying vendor being wrapped by this adapter
		*/
		public function getNativeClient ():object;
	}
?>