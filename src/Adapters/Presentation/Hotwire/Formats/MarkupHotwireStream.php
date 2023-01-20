<?php
	namespace Suphle\Adapters\Presentation\Hotwire\Formats;

	use Suphle\Response\Format\Markup;

	class MarkupHotwireStream extends BaseHotwireStream {

		/**
		 * @param {markupName}: It's not necessary to prefix with a slash
		*/
		public function __construct(

			string $handler, string $markupName, ?string $templateName = null
		) {

			$this->fallbackRenderer = new Markup($handler, $markupName, $templateName);
		}
	}
?>