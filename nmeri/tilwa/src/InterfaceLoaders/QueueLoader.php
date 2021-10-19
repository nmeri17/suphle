<?php
	namespace Tilwa\InterfaceLoaders;

	use Tilwa\App\{Bootstrap, BaseInterfaceLoader};

	use Illuminate\Queue\Capsule\Manager as QueueManager;

	class QueueLoader extends BaseInterfaceLoader {

		public function afterBind(QueueManager $initialized):void {

			$initialized->addConnection([
			    'driver' => 'beanstalkd',
			    'host' => 'localhost',
			    'queue' => 'default',
			]);
		}

		public function concrete():string {

			return QueueManager::class;
		}
	}
?>