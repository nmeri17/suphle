<?php

	namespace Tilwa\Middleware;

	use Tilwa\Contracts\Middleware;

	class FinalHandlerWrapper implements Middleware {

		private $manager;

		public function __construct (ResponseManager $manager) {

			$this->manager = $manager;
		}

		public function process ($request, $requestHandler) { // confirm that [requestHandler]==null

			$this->manager->handleValidRequest($request);

			$this->manager->afterRender();

			return $this->manager->getResponse();
		}
	}
?>