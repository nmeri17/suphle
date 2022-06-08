<?php
	namespace Tilwa\Tests\Integration\Modules\ModuleDescriptor;

	use Tilwa\Exception\Explosives\Generic\UnexpectedModules;

	use Tilwa\Tests\Mocks\Interactions\ModuleTwo;

	abstract class FailingCollection extends DescriptorCollection {

		public function test_will_throw_errors () {

			$this->expectException(UnexpectedModules::class); // then

			$this->getModuleFor(ModuleTwo::class); // when
		}
	}
?>