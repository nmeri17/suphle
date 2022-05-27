<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Services\Proxies\SystemModelEditCloaker;

	use Tilwa\Contracts\{Services\Decorators\SystemModelEdit, Hydration\ScopeHandlers\ModifyInjected};

	class SystemModelEditHandler extends BaseCallProxy implements ModifyInjected {

		private $cloakBuilder;

		public function __construct (SystemModelEditCloaker $cloakBuilder) {

			$this->cloakBuilder = $cloakBuilder;
		}

		/**
		 * @param {concrete} SystemModelEdit
		*/
		public function setCallDetails (object $concrete, string $caller):object {

			if ($method == "updateModels") // restrict this decorator from running on unrelated methods

				try {

					return $this->orm->runTransaction(function () use ($method) {

						return $this->triggerOrigin($method);

					}, $this->activeService->modelsToUpdate());
				}
				catch (Throwable $exception) {

					return $this->attemptDiffuse($exception, $method);
				}

			return $this->triggerOrigin($method, $arguments);
		}
	}
?>