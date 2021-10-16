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

			// avoid overwriting our own response
			$this->rawResponse += $this->router->getPreviousRenderer()->getRawResponse();
			// assumes that response is either a string or array
			// viewName, vmName // assign these from previous renderer
			
			return $this->renderHtml();
		}
	}
?>