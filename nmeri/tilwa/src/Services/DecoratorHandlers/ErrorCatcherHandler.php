<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Services\Proxies\ErrorCloakBuilder;

	use Tilwa\Contracts\{Services\Decorators\ServiceErrorCatcher, Hydration\ScopeHandlers\ModifyInjected};

	class ErrorCatcherHandler implements ModifyInjected {

		private $cloakBuilder;

		public function __construct (ErrorCloakBuilder $cloakBuilder) {

			$this->cloakBuilder = $cloakBuilder;
		}

		/**
		 * @param {concrete} ServiceErrorCatcher
		*/
		public function proxifyInstance (object $concrete, string $caller):object {

			$this->cloakBuilder->setTarget($concrete);

			return $this->cloakBuilder->buildClass();
		}
	}
?>