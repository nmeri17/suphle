<?php

	namespace Tilwa\Http\Response;

	use Tilwa\App\Container;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Controllers\ControllerManager;

	abstract class BaseResponseManager {
		
		abstract public function getResponse ();

		protected function getControllerManager(AbstractRenderer $renderer):ControllerManager {

			$container = $this->container;

			$controllerManager = $container->getClass(ControllerManager::class);

			$controllerManager->setController($container->getClass($renderer->getController()));

			return $controllerManager;
		}
	}
?>