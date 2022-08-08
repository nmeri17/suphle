<?php
	namespace Suphle\Adapters\Markups;

	use Suphle\Contracts\Presentation\{HtmlParser, TransphpormRenderer};

	use Transphporm\Builder;

	class Transphporm implements HtmlParser {

		public function parseAll (TransphpormRenderer $renderer):string {

			return (new Builder(
				
				file_get_contents($renderer->getMarkupPath()),

				file_get_contents($renderer->getTemplatePath())
			))
			->output($renderer->getRawResponse())->body;
		}
	}
?>