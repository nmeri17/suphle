<?php
	namespace Tilwa\Controllers\DecoratorHandlers;

	use Tilwa\Controllers\Proxies\SystemModelEditCloaker;

	use Tilwa\Contracts\{Services\Decorators\SystemModelEdit, Hydration\ScopeHandlers\ModifyInjected};

	class SystemModelEditHandler implements ModifyInjected {

		private $cloakBuilder;

		public function __construct (SystemModelEditCloaker $cloakBuilder) {

			$this->cloakBuilder = $cloakBuilder;
		}

		public function proxifyInstance (SystemModelEdit $concrete, string $caller) {

			$this->cloakBuilder->setTarget($concrete);

			return $this->cloakBuilder->buildClass();
		}
	}
?>