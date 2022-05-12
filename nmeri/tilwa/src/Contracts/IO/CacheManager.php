<?php
	namespace Tilwa\Contracts\IO;

	interface CacheManager {

		public function getItem (string $key);

		public function saveItem (string $key, $data):void;

		public function tagItem (string $key, $data):void;
	}
?>