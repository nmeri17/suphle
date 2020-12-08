<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	class Redirect extends Route {

		private $destination; // callable

		private $hard; // external redirect

		const RELOAD = 10;

		function __construct(string $destination, bool $hard=false) {

			$this->destination = $destination;

			$this->hard = $hard;
		}

		public function handleRedirects() {

			if ($this->destination === self::RELOAD ) $this->restorePrevPage = true;

			else if (is_callable($this->destination)) {

				// liquefy it so it can be cached if needed
				$this->destination = (new Serializer())->serialize($this->destination); // when called, it will be passed data from the associated controller to build the new url
			}

			return $this;
		}

		public function getRedirectDestination () {

			return (new Serializer)->unserialize($this->destination);
		}

		public function renderResponse() {

			if (
				(strpos($this->destination,'://') !== false) ||
				$this->hard
			)

				return header('Location: '. $destination);
			if (
				$destinationRoute = $this->router

				->findRoute( $this->destination, "get")
			)

				$router->setActiveRoute( $destinationRoute );
			/* Assumptions:
				- this route doesn't care about middlewares, validation etc
			*/
		}
	}
?>