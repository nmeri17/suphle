<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Presentation\{RendersMarkup, HtmlParser};

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\Services\Decorators\VariableDependencies;

	#[VariableDependencies(["setHtmlParser" ])]
	abstract class BaseBladeRenderer extends GenericRenderer implements RendersMarkup {

		protected string $markupName;

		protected ?string $filePath = null;

		protected HtmlParser $htmlParser;

		public function setHtmlParser (HtmlParser $parser):void {

			$this->htmlParser = $parser;
		}

		/**
		 * {@inheritdoc}
		*/
		public function setFilePath (string $markupPath):self {

			$this->filePath = $markupPath;

			return $this;
		}

		public function getMarkupName ():string {

			return $this->markupName;
		}
	}
?>