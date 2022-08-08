<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Meta;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Contracts\{Database\OrmDialect, Config\ModuleFiles};

	use Suphle\Config\AscendingHierarchy;

	use Suphle\File\FileSystemReader;

	use Suphle\Tests\Mocks\Interactions\{ModuleThree, ModuleOne};

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

				ModuleFiles::class => new AscendingHierarchy(__DIR__, _NAMESPACE__,

					$this->container->getClass(FileSystemReader::class)
				)
			]);
		}
	}
?>