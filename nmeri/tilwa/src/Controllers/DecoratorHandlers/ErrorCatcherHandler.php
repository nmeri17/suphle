<?php
	namespace Tilwa\Controllers\DecoratorHandlers;

	use Tilwa\Controllers\Proxies\ErrorCloakBuilder;

	use Tilwa\Contracts\{Services\Decorators\ServiceErrorCatcher, Hydration\ScopeHandlers\ModifyInjected};

	class ErrorCatcherHandler implements ModifyInjected {

		private $cloakBuilder;

		public function __construct (ErrorCloakBuilder $cloakBuilder) {

			$this->cloakBuilder = $cloakBuilder;
		}

		public function proxifyInstance (ServiceErrorCatcher $concrete, string $caller) {

			$this->cloakBuilder->setTarget($concrete);

			return $this->cloakBuilder->buildClass();
		}
	}
?>