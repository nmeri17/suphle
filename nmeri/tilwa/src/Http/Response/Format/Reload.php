<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\RouteManager;

	class Reload extends AbstractRenderer {

		protected $router;

		// change to 50* on validation error
		function __construct(string $handler, int $statusCode = 200) {
			
			$this->statusCode = $statusCode;

			$this->handler = $handler;
		}

		public function setDependencies( Container $container, string $controllerClass, RouteManager $router):self {

			$this->router = $router;

			return parent::setDependencies($container, $controllerClass);
		}

		public function render() {

			$this->rawResponse += $this->router->getPrevious()->rawResponse; // avoid overwriting our own response
			
			return $this->renderHtml();
		}
	}
?>