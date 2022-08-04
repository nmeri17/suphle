<?php
	namespace Suphle\Exception\Component;

	use Suphle\ComponentTemplates\BaseComponentEntry;

	use Suphle\Contracts\Config\{Transphporm as TConfig, ModuleFiles};

	class ComponentEntry extends BaseComponentEntry {

		private $viewConfig;

		public function __construct (ModuleFiles $fileConfig, ViewConfig $viewConfig) {

			parent::__construct($fileConfig);

			$this->viewConfig = $viewConfig;
		}

		protected function prefixName ():string {

			return "errors";
		}

		protected function getSources ():array {

			return [

				__DIR__. DIRECTORY_SEPARATOR . "Markup" => $this->fileConfig->getViewPath(),

				__DIR__. DIRECTORY_SEPARATOR . "Tss" => $this->viewConfig->getTssPath()
			];
		}
	}
?>