<?php
	namespace Suphle\Adapters\Cache;

	use Suphle\Contracts\IO\CacheManager;

	class InMemoryCache implements CacheManager {

		private $store = [], $tags = [];

		public function setupClient ():void {

			// error 404: no client to setup
		}

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

		public function deleteItem (string $key) {

			unset($this->store[$key]);
		}
	}
?>