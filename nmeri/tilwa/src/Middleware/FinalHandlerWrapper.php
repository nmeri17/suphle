<?php

	namespace Tilwa\Middleware;

	use Psr\Http\Server\RequestHandlerInterface;

	class FinalHandlerWrapper implements RequestHandlerInterface {

		private $manager;

		public function __construct (ResponseManager $manager) {

			$this->manager = $manager;
		}

		public function handle ($request, $requestHandler) {

			$this->manager->handleValidRequest();

			$this->manager->afterRender();

			return $this->manager->getResponse();
		}
	}
?>