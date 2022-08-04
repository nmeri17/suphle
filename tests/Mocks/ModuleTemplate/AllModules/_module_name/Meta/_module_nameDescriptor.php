<?php
	namespace AllModules\_module_name\Meta;

	use Suphle\Modules\ModuleDescriptor;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\Config\AscendingHierarchy;

	use Suphle\File\FileSystemReader;

	use ModuleInteractions\_module_name;

	class _module_nameDescriptor extends ModuleDescriptor {

		public function interfaceCollection ():string {

			return CustomInterfaceCollection::class;
		}

		public function exportsImplements():string {

			return _module_name::class;
		}

		public function globalConcretes ():array {

			return array_merge(parent::globalConcretes(), [

				ModuleFiles::class => new AscendingHierarchy(
					
					__DIR__, $this->container->getClass(FileSystemReader::class)
				)
			]);
		}
	}
?>