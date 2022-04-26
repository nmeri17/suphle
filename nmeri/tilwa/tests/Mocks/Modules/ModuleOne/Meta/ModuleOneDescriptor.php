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

		public function globalConcretes ():array {

			return array_merge(parent::globalConcretes(), [

				ModuleFiles::class => new AscendingHierarchy(__DIR__)
			]);
		}

		protected function registerConcreteBindings ():void {

			parent::registerConcreteBindings();

			$this->container->getClass(OrmDialect::class);
		}
	}
?>