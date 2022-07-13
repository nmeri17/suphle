<?php
	namespace Tilwa\Contracts\IO;

	interface CacheManager {

		public function setupClient ():void;

		public function getItem (string $key);

		public function deleteItem (string $key);

		public function saveItem (string $key, $data):void;

		public function tagItem (string $tagName, $data):void;
	}
?>