<?php
	namespace Tilwa\Tests\Integration\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Exception\Explosives\Generic\InvalidImplementor;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleTwo};

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class IncompatiblePairTest extends DescriptorCollection {

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleOne
			]);
		}

		public function test_will_throw_implementor_exception () {

			$this->expectException(InvalidImplementor::class); // then

			$this->getModuleFor(ModuleTwo::class); // when
		}
	}
?>