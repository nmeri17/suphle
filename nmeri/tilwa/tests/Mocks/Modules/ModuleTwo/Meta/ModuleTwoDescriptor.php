<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\{Database\OrmDialect, Config\ModuleFiles};

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

		protected function entityBindings ():void {

			parent::entityBindings();

			$this->container->whenTypeAny()->needsAny([

				ModuleFiles::class => new AscendingHierarchy(__DIR__)
			]);
		}
	}
?>