<?php
	namespace Suphle\Adapters\Markups;

	use Suphle\Contracts\Presentation\HtmlParser;

	use Transphporm\Builder;

	class Transphporm implements HtmlParser {

		public function parseAll (...$arguments):string {

			[$markup, $template, $data] = $arguments;

			return (new Builder(
				
				file_get_contents($markup), file_get_contents($template)
			))
			->output($data)->body;
		}
	}
?>