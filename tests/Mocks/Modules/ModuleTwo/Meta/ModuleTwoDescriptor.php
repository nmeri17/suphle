<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Meta;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\Config\AscendingHierarchy;

	use Suphle\File\FileSystemReader;

	use Suphle\Tests\Mocks\Interactions\{ModuleTwo, ModuleThree};

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

		public function globalConcretes ():array {

			return array_merge(parent::globalConcretes(), [

				ModuleFiles::class => new AscendingHierarchy(
					
					__DIR__, _NAMESPACE__,

					$this->container->getClass(FileSystemReader::class)
				)
			]);
		}
	}
?>