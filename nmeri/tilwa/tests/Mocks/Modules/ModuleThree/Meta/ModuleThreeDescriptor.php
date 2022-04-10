<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

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
	}
?>