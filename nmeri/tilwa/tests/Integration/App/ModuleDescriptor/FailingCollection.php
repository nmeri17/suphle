<?php
	namespace Tilwa\Tests\Integration\App\ModuleDescriptor;

	use Tilwa\Errors\UnexpectedModules;

	use Tilwa\Tests\Mocks\Interactions\ModuleTwo;

	class FailingCollection extends DescriptorCollection {

		public function test_will_throw_errors () {

			$this->expectException(UnexpectedModules::class); // then

			$this->getModuleFor(ModuleTwo::class); // when
		}
	}
?>