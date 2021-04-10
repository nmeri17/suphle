<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\RouteManager;

	class Reload extends AbstractRenderer {

		protected $router;

		function __construct(string $handler) {

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