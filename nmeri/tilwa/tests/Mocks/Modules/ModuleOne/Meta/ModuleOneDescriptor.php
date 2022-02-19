<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\Database\OrmDialect;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class ModuleOneDescriptor extends ModuleDescriptor {

		public function exportsImplements():string {

			return ModuleOne::class;
		}

		/**
		 * {@inheritdoc}
		*/
		public function interfaceCollection ():string {

			return CustomInterfaceCollection::class;
		}

		protected function entityBindings ():callable {

			return function (OrmDialect $ormDialect) {};
		}
	}
?>