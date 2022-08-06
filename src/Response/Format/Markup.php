<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Config\{Transphporm as ViewConfig, ModuleFiles};

	class Markup extends GenericRenderer {

		protected $viewName;

		private $wantsJson, $viewModel;

		private $fileConfig, $viewConfig;

		function __construct(string $handler, string $viewName, string $viewModel = null) {

			$this->handler = $handler;

			$this->viewName = $viewName;

			$this->viewModel = $viewModel;

			$this->setHeaders(200, ["Content-Type" => "text/html"]);
		}

		public function render():string {
			
			if ( !$this->wantsJson)

				return $this->renderHtml($this->viewName, $this->viewModel, $this->rawResponse);

			$this->setHeaders(200, ["Content-Type" => "application/json"]);

			return $this->renderJson();
		}

		public function setFilePaths (string $markupPath, string $viewModelPath):self {

			$this->markupPath = $markupPath;

			$this->viewModelPath = $viewModelPath;
		}

		protected function gvdxz () {

			$markup = trim($markup, "/");

			if (empty($template) && $viewConfig->inferFromViewName())

				$template = $markup;

			else $template = trim($template, "/");
		}

		public function dependencyMethods ():array {

			return array_merge(parent::dependencyMethods(), [

				"setFileConfig", "setViewConfig"
			]);
		}

		public function __construct (ModuleFiles $fileConfig, ViewConfig $viewConfig) {

			$this->fileConfig = $fileConfig;

			$this->viewConfig = $viewConfig;
		}$this->fileConfig->defaultViewPath() . $markup),

				$this->readFile($viewConfig->getTssPath() . $template

		/**
		 * Abstraction for mocking. Refactor the test with readFile to use the logic here instead
		*/
		public function readFile (string $fileName):string {

			return file_get_contents("$fileName.php");
		}

		public function setWantsJson():void {
			
			$this->wantsJson = true;
		}

		public function getViewName ():string {

			return $this->viewName;
		}

		public function getViewModelName ():string {

			return $this->viewModel;
		}
	}
?>