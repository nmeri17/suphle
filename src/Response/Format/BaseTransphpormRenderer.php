<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Presentation\TransphpormRenderer;

	use Suphle\Contracts\Config\{Transphporm as ViewConfig, ModuleFiles};

	abstract class BaseTransphpormRenderer extends GenericRenderer implements TransphpormRenderer {

		protected $markupName, $templateName, $markupPath, $templatePath,

		$fileConfig, $viewConfig;

		public function dependencyMethods ():array {

			return array_merge(parent::dependencyMethods(), [

				"setConfigs"
			]);
		}

		public function setConfigs (ModuleFiles $fileConfig, ViewConfig $viewConfig):TransphpormRenderer {

			$this->fileConfig = $fileConfig;

			$this->viewConfig = $viewConfig;

			return $this;
		}

		public function setFilePaths (string $markupPath, string $templatePath):self {

			$this->markupPath = $markupPath;

			$this->templatePath = $templatePath;

			return $this;
		}

		public function getMarkupPath ():string {

			return (

				$this->markupPath ?? $this->fileConfig->defaultViewPath()
			) .
			$this->markupName;
		}

		public function getTemplatePath ():string {

			return (

				$this->templatePath ?? $this->viewConfig->getTssPath()
			) .
			$this->safeGetTemplateName();
		}

		public function safeGetTemplateName ():string {

			if (
				empty($this->templateName) &&

				$this->viewConfig->inferFromViewName()
			)

				return $this->markupName;

			return $this->templateName;
		}
	}
?>