<?php
	namespace Suphle\IO\Cache;

	use Suphle\Contracts\IO\EnvAccessor;

	use Suphle\Hydration\BaseInterfaceLoader;

	use Suphle\Adapters\Cache\BoltDbCache;

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