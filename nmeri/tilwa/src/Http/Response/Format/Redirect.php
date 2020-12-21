<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	class Redirect extends Route {

		private $destination; // callable

		private $hard; // external redirect

		function __construct(Closure $destination, bool $hard=false) {

			$this->setDestination($destination);

			$this->hard = $hard;
		}

		public function setDestination($destination):void {

			$this->destination = (new Serializer())->serialize($destination);// liquefy it so it can be cached later under previous requests
		}

		public function getDestination () {

			return (new Serializer)->unserialize($this->destination);
		}

		public function renderResponse() {

			$destination = $this->resolveDestination($this->getDestination);

			return $this->relocate($destination);
		}

		private function relocate($destination) {

			if (
				(strpos($destination,'://') !== false) ||
				$this->hard
			)

				return header('Location: '. $destination);
			if (
				$localRoute = Router::findRoute( $destination, "get") // refactor. facades don't exist
			)

				return $localRoute->executeHandler() // refactor this to match the updates on this class

				->renderResponse(); // note: this navigation will bypass middlewares, validation
		}

		private function resolveDestination ($destination) {
			
			$parameters = App::wireActionParameters($destination, $this); // handle this special case in need of app

			return call_user_func_array($destination, $parameters);
		}
	}
?>