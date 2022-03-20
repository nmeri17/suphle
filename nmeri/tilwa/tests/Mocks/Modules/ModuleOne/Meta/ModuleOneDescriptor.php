<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\{Database\OrmDialect, Config\ModuleFiles};

	use Tilwa\Config\AscendingHierarchy;

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

		protected function entityBindings ():void {

			$this->container->whenTypeAny()->needsAny([

				ModuleFiles::class => new AscendingHierarchy(__DIR__)
			])

			->getClass(OrmDialect::class);
		}
	}
?>