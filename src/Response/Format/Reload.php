<?php
	namespace Suphle\Response\Format;

	use Suphle\Routing\RouteManager;

	use Suphle\Services\Decorators\VariableDependencies;

	use Suphle\Request\PayloadStorage;

	#[VariableDependencies([ "setRouter" ])]
	class Reload extends BaseTransphpormRenderer {

		protected RouteManager $router;

		public function __construct(protected string $handler) {

			$this->setHeaders(205, [ // Reset Content

				PayloadStorage::CONTENT_TYPE_KEY => "text/html"
			]);
		}

		public function setRouter (RouteManager $router):void {

			$this->router = $router;
		}

		public function render ():string {

			$renderer = $this->router->getPreviousRenderer();

			$this->markupPath = $renderer->getMarkupPath();

			$this->templatePath = $renderer->getTemplatePath();

			// keys clashes between current and previous should prioritise contents of the current response
			// assumes that response is an array
			$this->rawResponse = array_merge(

				$renderer->getRawResponse(), $this->rawResponse
			);
			
			return $this->htmlParser->parseAll($this);
		}
	}
?>