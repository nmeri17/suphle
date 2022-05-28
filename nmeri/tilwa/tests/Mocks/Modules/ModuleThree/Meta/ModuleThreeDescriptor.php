<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Meta;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Contracts\{Database\OrmDialect, Config\ModuleFiles};

	use Tilwa\Config\AscendingHierarchy;

	use Tilwa\File\FileSystemReader;

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

		public function globalConcretes ():array {

			return array_merge(parent::globalConcretes(), [

				ModuleFiles::class => new AscendingHierarchy(__DIR__,

					$this->container->getClass(FileSystemReader::class)
				)
			]);
		}
	}
?>