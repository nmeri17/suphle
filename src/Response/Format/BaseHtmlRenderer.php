<?php
	namespace Suphle\Response\Format;

	use Suphle\Contracts\Presentation\{RendersMarkup, HtmlParser};

	use Suphle\Services\Decorators\VariableDependencies;

	#[VariableDependencies(["setHtmlParser" ])]
	abstract class BaseHtmlRenderer extends GenericRenderer implements RendersMarkup {

		protected string $markupName;

		protected HtmlParser $htmlParser;

		public function setHtmlParser (HtmlParser $parser):void {

			$this->htmlParser = $parser;
		}

		/**
		 * {@inheritdoc}
		*/
		public function setMarkupName (string $markupName):void {

			$this->markupName = $markupName;
		}

		public function getMarkupName ():string {

			return $this->markupName;
		}
	}
?>