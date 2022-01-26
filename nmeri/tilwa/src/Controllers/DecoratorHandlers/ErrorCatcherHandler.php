<?php
	namespace Tilwa\Controllers\DecoratorHandlers;

	class ErrorCatcherHandler implements ModifyInjected {

		private $cloakBuilder;

		public function __construct (ErrorCloakBuilder $cloakBuilder) {

			$this->cloakBuilder = $cloakBuilder;
		}

		public function proxifyInstance ($concrete, string $caller) {

			$this->cloakBuilder->setTarget($concrete);

			return $this->cloakBuilder->buildClass();
		}
	}
?>