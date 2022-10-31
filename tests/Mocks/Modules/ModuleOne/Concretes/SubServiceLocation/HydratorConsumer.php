<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\SubServiceLocation;

	use Suphle\Hydration\Container;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\BCounter;

	class HydratorConsumer {

		public function __construct(protected Container $container)
  {
  }

		public function getSuperB ():BCounter {

			return $this->container->getClass(BCounter::class); // unable to see y
		}
	}
?>