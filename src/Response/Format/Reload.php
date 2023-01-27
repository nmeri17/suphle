<?php
	namespace Suphle\Response\Format;

	use Suphle\Routing\RouteManager;

	use Suphle\Services\Decorators\VariableDependencies;

	use Suphle\Request\PayloadStorage;

	#[VariableDependencies([ "setRouter" ])]
	class Reload extends BaseTransphpormRenderer {

		public const STATUS_CODE = 205; // Reset Content

		protected RouteManager $router;

		public function __construct(protected string $handler) {

			$this->setHeaders(self::STATUS_CODE, [

				PayloadStorage::CONTENT_TYPE_KEY => PayloadStorage::HTML_HEADER_VALUE
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