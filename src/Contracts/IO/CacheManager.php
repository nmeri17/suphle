<?php
	namespace Suphle\Contracts\IO;

	interface CacheManager {

		public function setupClient ():void;

		public function getItem (string $key, callable $storeOnAbsence = null);

		public function deleteItem (string $key);

		public function saveItem (string $key, $data):void;

		public function tagItem (string $tagName, $data):void;
	}
?>