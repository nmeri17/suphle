<?php
	namespace Suphle\Adapters\Cache;

	use Suphle\Contracts\IO\CacheManager;

	use Spiral\Goridge\RPC\RPC;

	use Spiral\RoadRunner\{Environment, KeyValue\Factory};

	use Psr\SimpleCache\CacheInterface;

	class BoltDbCache implements CacheManager {

		const TAG_KEY = "_reserved_key_tags";

		private $client, $activeNodeName;

		public function setupClient ():void {

			$this->client = new Factory(RPC::create(

				Environment::fromGlobals()->getRPCAddress()
			));
		}

		/**
		 * @param {storageName} Must match one of the storages defined in the key-value section of rr.yaml
		*/
		public function openDataNode (string $storageName):self {

			$this->activeNodeName = $storageName;

			return $this;
		}

		public function getItem (string $key) {
//var_dump(42, spl_object_hash($this) );
			return $this->getDataNode()->get($key);
		}

		public function saveItem (string $key, $data):void {

			$this->getDataNode()->set($key, $data);
		}

		public function getDataNode ():CacheInterface {

			return $this->client->select($this->activeNodeName);
		}

		public function tagItem (string $key, $data):void {

			$dataNode = $this->getDataNode();

			$allTags = $dataNode->get(self::TAG_KEY);

			if (is_null($allTags)) $allTags = [];

			if (!array_key_exists($key, $allTags))

				$allTags[$key] = [];

			$allTags[$key][] = $data;

			$dataNode->set(self::TAG_KEY, $allTags);
		}

		public function deleteItem (string $key) {

			$this->getDataNode()->set($key, null);
		}
	}
?>