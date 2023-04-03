<?php
	namespace Suphle\Adapters\Orms\Eloquent;

	use Suphle\ComponentTemplates\BaseComponentEntry;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\File\{FileSystemReader, FolderCloner};

	class ComponentEntry extends BaseComponentEntry {

		public const EJECT_DESTINATION = "database_folder",

		EJECT_NAMESPACE = "database_namespace";

		protected string $defaultFolderName = "AppModels"; // trying to use envAccessor for this results in an infinite loop

		public function __construct (
			protected readonly ModuleFiles $fileConfig,

			protected readonly FileSystemReader $fileSystemReader,

			protected readonly FolderCloner $folderCloner
		) {

			//
		}

		public function uniqueName ():string {

			return "SuphleEloquentTemplates";
		}

		protected function templatesLocation ():string {

			return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
		}

		/**
		 * {@inheritdoc}
		*/
		public function userLandMirror ():string {

			if (!array_key_exists(self::EJECT_DESTINATION, $this->inputArguments))

				return $this->defaultInstallPath();

			return $this->inputArguments[self::EJECT_DESTINATION];
		}

		public function defaultInstallPath ():string {

			return $this->fileConfig->getRootPath().

			$this->defaultFolderName . DIRECTORY_SEPARATOR;
		}

		public function eject ():void {

			$content = $this->getContentReplacements();

			$this->folderCloner->setEntryReplacements($content, [], $content)
			->transferFolder(

				$this->templatesLocation(), $this->userLandMirror()
			);
		}

		protected function getContentReplacements ():array {

			if (array_key_exists(self::EJECT_NAMESPACE, $this->inputArguments))

				$namespace = $this->inputArguments[self::EJECT_NAMESPACE];

			else { // acceptable for single-word/root namespaces

				preg_match(
					"/[\\/\\\\](\w+)$/i", // escaped version of /[\/\\](\w+)$/i

					rtrim($this->userLandMirror(), "\\/"), $mirrorName
				);

				$namespace = $mirrorName[1]; // lift last path part
			}

			return [ "_". self::EJECT_NAMESPACE => $namespace ];
		}
	}
?>