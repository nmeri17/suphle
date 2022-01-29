<?php

	namespace Tilwa\Contracts\Database;

	interface Orm {

		public function getConnection ();

		public function runTransaction(callable $queries, array $lockModels = [], bool $hardLock = false);

		public function registerObservers(array $observers):void;

		// inserts into the DB rather than returning mere copies
		public function factoryProduce ($model, $amount):void;

		public function factoryLine ($model, int $amount, array $customAttributes);

		public function findAny ($model);

		public function findAnyMany ($model, int $amount):array;

		public function saveOne ($model):void;

		/**
		 * @return A builder/query object, with the filters applied
		*/
		public function selectFields ($builder, array $filters);

		public function hardLock( $model):void;

		public function softLock( $model):void;
	}
?>