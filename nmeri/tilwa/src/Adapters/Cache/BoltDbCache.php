<?php
	namespace Tilwa\Adapters\Cache;

	use Tilwa\Contracts\IO\{CacheManager, EnvAccessor};

	use Spiral\Goridge\RPC\RPC;

	use Spiral\RoadRunner\KeyValue\Factory;

	use Psr\SimpleCache\CacheInterface;

	class BoltDbCache implements CacheManager {

		const TAG_KEY = "_reserved_key_tags";

		private $client, $envAccessor, $activeNodeName;

		public function __construct (EnvAccessor $envAccessor) {

			$this->envAccessor = $envAccessor;
		}

		public function setupClient ():void {

			$this->client = new Factory(RPC::create(

				$this->envAccessor->getField("RR_RPC")
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