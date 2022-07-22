<?php
	namespace Suphle\Config;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\File\FileSystemReader;

	/**
	 * Note: Returned paths have trailing slashes
	*/
	class AscendingHierarchy implements ModuleFiles {

		private $descriptorPath, $systemReader;

		public function __construct (string $descriptorPath, FileSystemReader $systemReader) {

			$this->descriptorPath = $descriptorPath;

			$this->systemReader = $systemReader;
		}

		/**
		 * {@inheritdoc}
		*/
		public function getRootPath ():string {

			return $this->systemReader->pathFromLevels(

				$this->descriptorPath, "", 2
			);
		}

		/**
		 * {@inheritdoc}
		*/
		public function activeModulePath ():string {

			return $this->systemReader->pathFromLevels(

				$this->descriptorPath, "", 1
			);
		}

		/**
		 * {@inheritdoc}
		*/
		public function getViewPath ():string {

			return $this->activeModulePath(). "Markup" . DIRECTORY_SEPARATOR;
		}

		/**
		 * {@inheritdoc}
		*/
		public function getImagePath ():string {

			return $this->activeModulePath(). "Images" . DIRECTORY_SEPARATOR;
		}
	}
?>