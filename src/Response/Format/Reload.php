<?php
	namespace Suphle\Response\Format;

	use Suphle\Routing\RouteManager;

	use Suphle\Services\Decorators\VariableDependencies;

	#[VariableDependencies([ "setRouter" ])]
	class Reload extends BaseTransphpormRenderer {

		protected $router;

		public function __construct(string $handler) {

			$this->handler = $handler;

			$this->setHeaders(205, ["Content-Type" => "text/html"]); // Reset Content
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