<?php
	namespace Suphle\Adapters\Cache;

	use Suphle\Contracts\IO\CacheManager;

	use Exception;

	class InMemoryCache implements CacheManager {

		protected array $store = [], $tags = [];

		public function setupClient ():void {

			// error 404: no client to setup
		}

		public function getItem (string $key, callable $storeOnAbsence = null) {

			if (array_key_exists($key, $this->store))

				return $this->store[$key];

			if (is_null($storeOnAbsence)) return;

			$toStore = $storeOnAbsence();

			if (is_null($toStore))

				throw new Exception("Cache data source cannot return null");

			$this->saveItem($key, $toStore);

			return $toStore;
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