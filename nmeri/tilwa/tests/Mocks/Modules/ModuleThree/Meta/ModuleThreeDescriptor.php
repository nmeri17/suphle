<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\{Database\OrmDialect, Config\ModuleFiles};

	use Tilwa\Config\AscendingHierarchy;

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

		protected function entityBindings ():void {

			parent::entityBindings();

			$this->container->whenTypeAny()->needsAny([

				ModuleFiles::class => new AscendingHierarchy(__DIR__)
			]);
		}
	}
?>