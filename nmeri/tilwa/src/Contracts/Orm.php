<?php

	namespace Tilwa\Contracts;

	use Models\User;

	interface Orm {

		protected $connection;

		private $credentials;

		public function findOne(string $model, int $id);

		private setConnection():self;

		public function isModel( string $class): bool;
	}
?>