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

			// avoid overwriting our own response
			$this->rawResponse += $renderer->getRawResponse();
			// assumes that response is either a string or array
			
			return $this->renderHtml($renderer->getViewName(), $renderer->getViewModelName(), $this->rawResponse);
		}
	}
?>