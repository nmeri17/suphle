<?php

	namespace Tilwa\Contracts;

	interface Orm {

		protected $connection;

		private $credentials;

		public function findOne(string $model, int $id);

		private setConnection():self;

		public function isModel( string $class): bool;

		private function getConnection ();
	}
?>