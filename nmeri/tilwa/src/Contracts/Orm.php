<?php

	namespace Tilwa\Contracts;

	interface Orm {

		protected $connection;

		private $credentials;

		public function findOne(string $model, int $id);

		private function setConnection():self;

		public function isModel( string $class): bool;

		private function getConnection ();

		// @param {callback} action to perform once the query being sent is intercepted A. Should be passed to the underlying parameter catcher who will supply the prepared parameters being sent B. Accepts another closure C that receives B as argument in order to execute A
		public function setTrap(Closure $callback);

		public function builderWhere( string $modelName, $modelId, string $columnName)/*:builder*/;
	}
?>