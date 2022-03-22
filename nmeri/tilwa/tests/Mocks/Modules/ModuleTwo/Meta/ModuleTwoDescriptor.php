<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\Config\ModuleFiles;

	use Tilwa\Config\AscendingHierarchy;

	use Tilwa\Tests\Mocks\Interactions\{ModuleTwo, ModuleThree};

	class ModuleTwoDescriptor extends ModuleDescriptor {

		public function exportsImplements():string {

			return ModuleTwo::class;
		}

		public function expatriateNames ():array {

			return [ModuleThree::class];
		}

		/**
		 * {@inheritdoc}
		*/
		public function interfaceCollection ():string {

			return CustomInterfaceCollection::class;
		}

		public function fileConfig ():ModuleFiles {

			return new AscendingHierarchy (__DIR__);
		}
	}
?>