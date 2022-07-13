<?php
	namespace Tilwa\Queues;

	use Tilwa\Contracts\IO\EnvAccessor;

	use Tilwa\Hydration\BaseInterfaceLoader;

	use Tilwa\Adapters\Queues\BoltDbQueue;

	class AdapterLoader extends BaseInterfaceLoader {

		private $envAccessor;

		public function __construct (EnvAccessor $envAccessor) {

			$this->envAccessor = $envAccessor;
		}

		public function afterBind ($initialized):void {

			$initialized->configureNative();

			$initialized->setActiveQueue(

				$this->envAccessor->getField("DEFAULT_QUEUE_NAME")
			);
		}

		public function concreteName ():string {

			return BoltDbQueue::class;
		}
	}
?>