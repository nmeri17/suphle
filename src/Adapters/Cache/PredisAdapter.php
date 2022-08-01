<?php
	namespace Suphle\Adapters\Cache;

	use Suphle\Contracts\{IO\CacheManager, Config\CacheClient as CacheConfig};

	use Predis\Client;

	class PredisAdapter implements CacheManager {

		const TAG_KEY = "_reserved_key_tags";

		private $client, $cacheConfig;

		public function __construct (CacheConfig $cacheConfig) {

			$this->cacheConfig = $cacheConfig;
		}

		public function setupClient ():void {

			$this->client = new Client($this->cacheConfig->getCredentials());
		}

		public function getItem (string $key) {

			return $this->client->get($key);
		}

		public function saveItem (string $key, $data):void {

			$this->client->set($key, $data);
		}

		public function tagItem (string $key, $data):void {

			$dataNode = $this->client;

			$allTags = $dataNode->get(self::TAG_KEY);

			if (is_null($allTags)) $allTags = [];

			if (!array_key_exists($key, $allTags))

				$allTags[$key] = [];

			$allTags[$key][] = $data;

			$dataNode->set(self::TAG_KEY, $allTags);
		}

		public function deleteItem (string $key) {

			$this->client->set($key, null);
		}
	}
?>