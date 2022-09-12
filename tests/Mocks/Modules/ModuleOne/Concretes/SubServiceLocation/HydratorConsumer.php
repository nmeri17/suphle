<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\SubServiceLocation;

	use Suphle\Hydration\Container;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

	class HydratorConsumer {

		protected $container;

		public function __construct (Container $container) {

			$this->container = $container;
		}

		public function getSuperB ():BCounter {

			return $this->container->getClass(BCounter::class); // unable to see y
		}
	}
?>