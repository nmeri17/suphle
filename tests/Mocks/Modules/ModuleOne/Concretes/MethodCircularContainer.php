<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Suphle\Hydration\Container;

	class MethodCircularContainer {

		public function __construct(private readonly Container $container)
  {
  }

		public function loadFromContainer ():MethodCircularConstructor {

			return $this->container->getClass(MethodCircularConstructor::class);
		}
	}
?>