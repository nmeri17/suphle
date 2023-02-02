<?php
	namespace Suphle\Adapters\Presentation\Blade;

	use Suphle\Contracts\Presentation\{HtmlParser, RendersMarkup};

	use Suphle\Contracts\Config\Blade as BladeConfig;

	class BladeParser implements HtmlParser {

		public function __construct (protected readonly BladeConfig $bladeConfig) {

			//
		}

		public function findInPath (string $markupPath):void {

			$this->bladeConfig->addViewPath($markupPath);
		}

		public function parseAll (RendersMarkup $renderer):string {

			$this->bladeConfig->setViewFactory();

			return $this->bladeConfig->getViewFactory()->make(

				$renderer->getMarkupName(), $renderer->getRawResponse()
			)->render();
		}
	}
?>