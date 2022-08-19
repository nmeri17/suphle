<?php
	namespace Suphle\Bridge\Laravel;

	use Suphle\ComponentTemplates\BaseComponentEntry;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\File\FileSystemReader;

	class ComponentEntry extends BaseComponentEntry {

		private $remoteConfig;

		public function __construct (

			ModuleFiles $fileConfig, FileSystemReader $fileSystemReader,

			ConfigDownloader $remoteConfig
		) {

			parent::__construct($fileConfig, $fileSystemReader);

			$this->remoteConfig = $remoteConfig;
		}

		public function uniqueName ():string {

			return "SuphleLaravelTemplates";
		}

		protected function templatesLocation ():string {

			return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
		}

		public function eject ():void {

			parent::eject();

			$this->remoteConfig->setFilePath(

				$this->userLandMirror() . "config/app.php"
			)->getDomainObject();
		}
	}
?>