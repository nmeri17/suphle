<?php
	namespace Suphle\Adapters\Markups;

	use Suphle\Contracts\Presentation\{HtmlParser, TransphpormRenderer, RendersMarkup};

	use Transphporm\Builder;

	class Transphporm implements HtmlParser {

		/**
		 * @param {renderer}: TransphpormRenderer
		*/
		public function parseAll (RendersMarkup $renderer):string {

			return (new Builder(
				
				file_get_contents($renderer->getMarkupPath() . ".php"),

				file_get_contents($renderer->getTemplatePath() . ".php")
			))
			->output($renderer->getRawResponse())->body;
		}
	}
?>