<?php
	namespace Suphle\Queues;

	use Suphle\Contracts\IO\EnvAccessor;

	use Suphle\Hydration\BaseInterfaceLoader;

	use Suphle\Adapters\Queues\BoltDbQueue;

	class AdapterLoader extends BaseInterfaceLoader {

		public function __construct(private readonly EnvAccessor $envAccessor)
  {
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