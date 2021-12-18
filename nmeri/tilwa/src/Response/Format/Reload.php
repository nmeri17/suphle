<?php
	namespace Tilwa\Response\Format;

	use Tilwa\Routing\RouteManager;

	class Reload extends AbstractRenderer {

		protected $router;

		function __construct(string $handler) {

			$this->handler = $handler;

			$this->setHeaders(200, ["Content-Type" => "text/html"]); // or 205 Reset Content
		}

		public function setDependencies( Container $container, string $controllerClass, RouteManager $router):self {

			$this->router = $router;

			return parent::setDependencies($container, $controllerClass);
		}

		public function render() {

			$renderer = $this->router->getPreviousRenderer();

			// keys clashes between current and previous should prioritise contents of the current response
			// assumes that response is an array
			$this->rawResponse = array_merge($renderer->getRawResponse(), $this->rawResponse);
			
			return $this->renderHtml($renderer->getViewName(), $renderer->getViewModelName(), $this->rawResponse);
		}
	}
?>