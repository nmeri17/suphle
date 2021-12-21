<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Tests\Mocks\Interactions\{ModuleThree/*, ModuleThree*/};

	class ModuleThreeDescriptor extends ModuleDescriptor {

		public function exportsImplements():string {

			return ModuleThree::class;
		}

		public function expatriateNames ():array {

			return [/*ModuleThree::class*/]; // possibly four
		}

		/**
		 * {@inheritdoc}
		*/
		public function interfaceCollection ():string {

			return CustomInterfaceCollection::class;
		}
	}
?>