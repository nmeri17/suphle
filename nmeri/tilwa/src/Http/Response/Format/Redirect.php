<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	use Tilwa\App\Bootstrap;

	class Redirect extends Route {

		private $destination; // callable

		function __construct(string $handler, Closure $destination, string $httpMethod) {

			$this->setDestination($destination);
		}

		public function setDestination($destination):void {

			$this->destination = (new Serializer())->serialize($destination);// liquefy it so it can be cached later under previous requests
		}

		public function getDestination () {

			return (new Serializer)->unserialize($this->destination);
		}

		public function renderResponse(Bootstrap $module) {
			
			$callable = $this->getDestination(); // hoping this returns a callable, although the serialization shouldn't have a use case. Redirect routes aren't gonna get stored in the previousRoutes property, anyway. I think
			// just review sha

			$parameters = $module->getMethodParameters($callable);

			return header('Location: '. call_user_func_array($callable, $parameters));
		}
	}
?>