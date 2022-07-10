<?php
	namespace Tilwa\Tests\Integration\Generic;

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleOne, ModuleTwo};

	use Tilwa\Tests\Mocks\Modules\{ModuleTwo\Meta\ModuleTwoDescriptor, ModuleThree\Meta\ModuleThreeDescriptor, ModuleOne\Meta\ModuleOneDescriptor};

	trait TestsModuleList {

		protected $moduleOne, $moduleTwo, $moduleThree;

		protected function setAllDescriptors ():void {

			$this->setModuleOne();

			$this->setModuleThree();

			$this->setModuleTwo();
		}

		protected function getAllDescriptors ():array {

			return [
				$this->moduleOne, $this->moduleTwo,

				$this->moduleThree
			];
		}

		protected function setModuleThree ():void {

			$this->moduleThree = (new ModuleThreeDescriptor(new Container))

			->sendExpatriates([

				ModuleOne::class => $this->moduleOne
			]);
		}

		protected function setModuleOne ():void {

			$this->moduleOne = new ModuleOneDescriptor(new Container);
		}

		protected function setModuleTwo ():void {

			$this->moduleTwo = (new ModuleTwoDescriptor(new Container))

			->sendExpatriates([

				ModuleThree::class => $this->moduleThree
			]);
		}
	}
?>