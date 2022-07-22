<?php
	namespace Suphle\Tests\Integration\Modules\ModuleDescriptor;

	use Suphle\Exception\Explosives\Generic\UnexpectedModules;

	use Suphle\Tests\Mocks\Interactions\ModuleTwo;

	abstract class FailingCollection extends DescriptorCollection {

		public function test_will_throw_errors () {

			$this->expectException(UnexpectedModules::class); // then

			$this->getModuleFor(ModuleTwo::class); // when
		}
	}
?>