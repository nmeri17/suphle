<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\Config\ModuleFiles;

	use Tilwa\Config\AscendingHierarchy;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree/*, ModuleThree*/};

	class ModuleThreeDescriptor extends ModuleDescriptor {

		public function exportsImplements():string {

			return ModuleThree::class;
		}

		public function expatriateNames ():array {

			return [ModuleOne::class];
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