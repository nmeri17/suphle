<?php
	namespace Suphle\Tests\Unit\Modules\InvalidDependencies;

	use Suphle\Hydration\Container;

	use Suphle\Exception\Explosives\DevError\UnexpectedModules;

	use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

	use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Meta\ModuleTwoDescriptor;

	class SurplusDependenciesTest extends DescriptorCollection {

		protected function setUp ():void {}

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleThree,

				ModuleOne::class => $this->moduleOne
			]);
		}

		public function test_will_reject_surplus_dependencies () {

			$this->expectException(UnexpectedModules::class); // then

			parent::setUp(); // when

			// given @see module creation
		}
	}
?>