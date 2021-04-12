<?php

	namespace Tilwa\Contracts;

	interface CacheManager {

		public function get();

		public function save();
	}
?>