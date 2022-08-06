<?php
	namespace Suphle\Response\Format;

	use Suphle\Routing\RouteManager;

	class Reload extends GenericRenderer {

		protected $router;

		function __construct(string $handler) {

			$this->handler = $handler;

			$this->setHeaders(205, ["Content-Type" => "text/html"]); // Reset Content
		}

		public function dependencyMethods ():array {

			return array_merge(parent::dependencyMethods(), [

				"setRouter"
			]);
		}

		public function setRouter (RouteManager $router):void {

			$this->router = $router;
		}

		public function render ():string {

			$renderer = $this->router->getPreviousRenderer();

			// keys clashes between current and previous should prioritise contents of the current response
			// assumes that response is an array
			$this->rawResponse = array_merge(

				$renderer->getRawResponse(), $this->rawResponse
			);
			
			return $this->renderHtml(

				$renderer->getViewName(), $renderer->getViewModelName(),

				$this->rawResponse
			);
		}
	}
?>