<?php
	namespace Tilwa\Contracts\Database;

	interface OrmTester {

		protected function getConnection ($connection = null);
	}
?>