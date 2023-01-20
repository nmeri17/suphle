<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Presentation\{TransphpormRenderer, HtmlParser};

	use Suphle\Contracts\Config\{Transphporm as ViewConfig, ModuleFiles};

	use Suphle\Services\Decorators\VariableDependencies;

	#[VariableDependencies([

		"setConfigs", "setHtmlParser"
	])]
	abstract class BaseTransphpormRenderer extends GenericRenderer implements TransphpormRenderer {

		protected string $markupName;

		protected ?string $markupPath, $templatePath, $templateName;

		protected ModuleFiles $fileConfig;
		
		protected ViewConfig $viewConfig;

		protected HtmlParser $htmlParser;

		public function setConfigs (ModuleFiles $fileConfig, ViewConfig $viewConfig):TransphpormRenderer {

			$this->fileConfig = $fileConfig;

			$this->viewConfig = $viewConfig;

			return $this;
		}

		public function setHtmlParser (HtmlParser $parser):void {

			$this->htmlParser = $parser;
		}

		/**
		 * {@inheritdoc}
		*/
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