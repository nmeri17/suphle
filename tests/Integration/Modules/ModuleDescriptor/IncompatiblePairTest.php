<?php
	namespace Suphle\Tests\Integration\Modules\ModuleDescriptor;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Explosives\Generic\InvalidImplementor;

	use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleTwo};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class IncompatiblePairTest extends DescriptorCollection {

		protected function setUp ():void {}

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleOne
			]);
		}

		public function test_will_throw_implementor_exception () {

			$this->expectException(InvalidImplementor::class); // then

			parent::setUp();

			$this->getModuleFor(ModuleTwo::class); // when
		}
	}
?>