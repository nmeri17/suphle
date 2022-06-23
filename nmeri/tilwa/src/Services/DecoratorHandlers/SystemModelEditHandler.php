<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\{Services\Decorators\SystemModelEdit, Database\OrmDialect, Config\DecoratorProxy};

	use Tilwa\Hydration\Structures\ObjectDetails;

	use ProxyManager\Proxy\AccessInterceptorInterface;

	use Throwable;

	class SystemModelEditHandler extends BaseInjectionModifier {

		private $ormDialect, $errorDecoratorHandler;

		public function __construct (
			OrmDialect $ormDialect, ErrorCatcherHandler $errorDecoratorHandler,

			DecoratorProxy $proxyConfig, ObjectDetails $objectMeta
		) {

			$this->ormDialect = $ormDialect;

			$this->errorDecoratorHandler = $errorDecoratorHandler;

			parent::__construct($proxyConfig, $objectMeta);
		}

		/**
		 * @param {concrete} SystemModelEdit
		*/
		public function examineInstance (object $concrete, string $caller):object {

			return $this->getProxy($concrete);
		}

		public function getMethodHooks ():array {

			return [

				"updateModels" => [$this, "wrapUpdateModels"]
			];
		}

		public function wrapUpdateModels (
			AccessInterceptorInterface $proxy, SystemModelEdit $concrete,

			string $methodName, array $argumentList
		) {

			try {

				return $this->ormDialect->runTransaction(function () use ($concrete) {

					return $concrete->updateModels();

				}, $concrete->modelsToUpdate());
			}
			catch (Throwable $exception) {

				return $this->errorDecoratorHandler->attemptDiffuse(

					$exception, $proxy, $concrete, $methodName
				);
			}
		}
	}
?>