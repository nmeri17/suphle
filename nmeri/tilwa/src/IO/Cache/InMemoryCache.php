<?php
	namespace Tilwa\IO\Cache;

	use Tilwa\Contracts\IO\CacheManager;

	class InMemoryCache implements CacheManager {

		private $store = [], $tags;

		public function getItem (string $key) {

			if (array_key_exists($key, $this->store))

				return $this->store[$key];
		}

		public function saveItem (string $key, $data):void {

			$this->store[$key] = $data;
		}

		public function tagItem (string $key, $data):void {

			if (!array_key_exists($key, $this->tags))

				$this->tags[$key] = [];

			$this->tags[$key][] = $data;
		}
	}
?>