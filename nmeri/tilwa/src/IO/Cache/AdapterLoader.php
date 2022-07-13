<?php
	namespace Tilwa\IO\Cache;

	use Tilwa\Contracts\IO\EnvAccessor;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Adapters\Cache\BoltDbCache;

	class AdapterLoader extends BaseInterfaceLoader {

		private $envAccessor;

		public function __construct (EnvAccessor $envAccessor) {

			$this->envAccessor = $envAccessor;
		}

		public function afterBind ($initialized):void {

			$initialized->setupClient();

			$initialized->openDataNode( // so, dev can start using it right away

				$this->envAccessor->getField("DEFAULT_CACHE_NAME")
			);
		}

		public function concreteName ():string {

			return BoltDbCache::class;
		}
	}
?>