<?php
	namespace Tilwa\InterfaceLoaders;

	use Tilwa\App\{Bootstrap, BaseInterfaceLoader};

	use Tilwa\Adapters\Queues\LaravelQueue;

	class QueueLoader extends BaseInterfaceLoader {

		public function afterBind(LaravelQueue $initialized):void {

			$initialized->addConnection([
			    "driver" => "beanstalkd",
			    
			    "host" => "localhost",
			    
			    "queue" => "default",
			]);
		}

		public function concrete():string {

			return LaravelQueue::class;
		}
	}
?>