<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\{Services\Decorators\SystemModelEdit, Database\OrmDialect, Config\DecoratorProxy};

	class SystemModelEditHandler extends BaseDecoratorHandler {

		private $ormDialect, $errorDecoratorHandler;

		public function __construct (
			OrmDialect $ormDialect, ErrorCatcherHandler $errorDecoratorHandler,

			DecoratorProxy $proxyConfig
		) {

			$this->ormDialect = $ormDialect;

			$this->errorDecoratorHandler = $errorDecoratorHandler;

			parent::__construct($proxyConfig);
		}

		/**
		 * @param {concrete} SystemModelEdit
		*/
		public function examineInstance (object $concrete, string $caller):object {

			return $this->getProxy($concrete);
		}

		public function getMethodHooks ():array {

			return [

				"updateResource" => [$this, "wrapUpdateModels"]
			];
		}

		public function wrapUpdateModels (object $concrete, string $methodName, array $argumentList) {

			try {

				return $this->ormDialect->runTransaction(function () use ($concrete) {

					return $concrete->updateModels();

				}, $concrete->modelsToUpdate());
			}
			catch (Throwable $exception) {

				return $this->errorDecoratorHandler->attemptDiffuse(

					$exception, $concrete, $method
				);
			}
		}
	}
?>