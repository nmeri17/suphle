<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\{Services\Decorators\MultiUserModelEdit, Database\OrmDialect, Config\DecoratorProxy};

	use Tilwa\Queues\AdapterManager;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Hydration\Structures\ObjectDetails;

	use Tilwa\Exception\Explosives\EditIntegrityException;

	use ProxyManager\Proxy\AccessInterceptorInterface;

	use Throwable, DateTime;

	/**
	 * The idea is that the last updater should invalidate whatever those with current copies of the page are both looking at or trying to update
	*/

	class MultiUserEditHandler extends BaseInjectionModifier {

		const INTEGRITY_KEY = "_collision_protect", // submitted form/payload is expected to contain this key

		DATE_FORMAT = "Y-m-d H:i:s";

		private $ormDialect, $queueManager, $payloadStorage,

		$errorDecoratorHandler;

		public function __construct (
			OrmDialect $ormDialect, AdapterManager $queueManager,

			PayloadStorage $payloadStorage, ErrorCatcherHandler $errorDecoratorHandler,

			DecoratorProxy $proxyConfig, ObjectDetails $objectMeta
		) {

			$this->ormDialect = $ormDialect;

			$this->queueManager = $queueManager;

			$this->payloadStorage = $payloadStorage;

			$this->errorDecoratorHandler = $errorDecoratorHandler; // composing instead of extending to decouple constructor dependencies

			parent::__construct($proxyConfig, $objectMeta);
		}

		/**
		 * @param {concrete} MultiUserModelEdit
		*/
		public function examineInstance (object $concrete, string $caller):object {

			return $this->getProxy($concrete);
		}

		public function getMethodHooks ():array {

			return [ // we're not wrapping "getResource" since we want request rermination if getting editable resource failed; there's nothing to fallback on

				"updateResource" => [$this, "wrapUpdateResource"]
			];
		}

		/**
		 * @return mixed. Operation result
		 * 
		 * @throws EditIntegrityException
		*/
		public function wrapUpdateResource (
			AccessInterceptorInterface $proxy, MultiUserModelEdit $concrete,
			string $methodName, array $argumentList
		) {

			if (!$this->payloadStorage->hasKey(self::INTEGRITY_KEY))

				throw new EditIntegrityException;

			$currentVersion = $concrete->getResource();

			if (!$currentVersion->includesEditIntegrity(

				$this->payloadStorage->getKey(self::INTEGRITY_KEY)
			)) // this is the heart of the entire decoration

				throw new EditIntegrityException;

			try {

				return $this->ormDialect->runTransaction(function () use ($currentVersion, $concrete) {

					$result = $concrete->updateResource(); // user's incoming changes

					$currentVersion->nullifyEditIntegrity(

						new DateTime(self::DATE_FORMAT)
					);

					return $result;

				}, [$currentVersion], true);
			}
			catch (Throwable $exception) {

				return $this->errorDecoratorHandler->attemptDiffuse(

					$exception, $proxy, $concrete, $methodName
				);
			}
		}
	}
?>