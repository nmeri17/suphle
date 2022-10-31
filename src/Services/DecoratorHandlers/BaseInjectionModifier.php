<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Suphle\Contracts\Config\DecoratorProxy;

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use ProxyManager\{Factory\AccessInterceptorValueHolderFactory as AccessInterceptor, Proxy\AccessInterceptorInterface};

	/**
	 * Helper class for handlers that want to wrap some/all methods
	*/
	abstract class BaseInjectionModifier implements ModifyInjected {

		protected $methodHooks = [], $proxyConfig, $objectMeta;

		public function __construct (DecoratorProxy $proxyConfig, ObjectDetails $objectMeta) {

			$this->proxyConfig = $proxyConfig;

			$this->objectMeta = $objectMeta;
		}

		public function getMethodHooks ():array {

			return $this->methodHooks;
		}

		/**
		 * @return Object proxy
		*/
		protected function allMethodAction (object $concrete, callable $action):AccessInterceptorInterface {

			foreach (
				$this->objectMeta->getPublicMethods($concrete::class)

				as $methodName
			)

				$this->methodHooks[$methodName] = $action;

			return $this->getProxy($concrete);
		}

		protected function getProxy (object $concrete):AccessInterceptorInterface {

			return (new AccessInterceptor(

				$this->proxyConfig->getConfigClient()
			))
			->createProxy( $concrete,

				$this->convertActionsToHook( $this->getMethodHooks())
				// no argument 3 since we don't care about post hooks
			);
		}

		/**
		 * @param {baseActions} [method => function ($proxy, object $concrete, string $methodName, array $argumentList)]
		*/
		private function convertActionsToHook (array $baseActions):array {

			$hookers = [];

			foreach ($baseActions as $hooker => $action) // handlers with same method won't clash since we're using unique proxies for each handler

				$hookers[$hooker] = function ($proxy, $concrete, $calledMethod, $parameters, &$earlyReturn) use ($action) { // hooker == calledMethod

					$earlyReturn = true; // since handlers want to take responsibility of calling underlying concrete, not this library

					return call_user_func_array($action, [

						$proxy, $concrete,

						$calledMethod, $parameters
					]);
				};

			return $hookers;
		}

		protected function triggerOrigin (object $concrete, string $method, array $arguments) {

			return call_user_func_array(
			
				[$concrete, $method], $arguments
			);
		}
	}
?>