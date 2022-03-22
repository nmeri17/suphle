<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Services\Proxies\MultiUserModelEditCloaker;

	use Tilwa\Contracts\{Services\Decorators\MultiUserModelEdit, Hydration\ScopeHandlers\ModifyInjected};

	class MultiUserEditHandler implements ModifyInjected {

		private $cloakBuilder;

		public function __construct (MultiUserModelEditCloaker $cloakBuilder) {

			$this->cloakBuilder = $cloakBuilder;
		}

		public function proxifyInstance (MultiUserModelEdit $concrete, string $caller) {

			$this->cloakBuilder->setTarget($concrete);

			return $this->cloakBuilder->buildClass();
		}
	}
?>