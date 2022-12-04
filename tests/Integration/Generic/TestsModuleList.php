<?php
	namespace Suphle\Tests\Integration\Generic;

	use Suphle\Hydration\Container;

	use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleOne, ModuleTwo};

	use Suphle\Tests\Mocks\Modules\{ModuleTwo\Meta\ModuleTwoDescriptor, ModuleThree\Meta\ModuleThreeDescriptor, ModuleOne\Meta\ModuleOneDescriptor};

	trait TestsModuleList {

		protected $moduleOne;
  protected $moduleTwo;
  protected $moduleThree;

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