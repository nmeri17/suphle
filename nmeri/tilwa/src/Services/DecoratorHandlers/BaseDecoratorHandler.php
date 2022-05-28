<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Hydration\ScopeHandlers\ModifyInjected;

	use Tilwa\Contracts\Config\DecoratorProxy;

	use Tilwa\Hydration\Container;

	use ProxyManager\Factory\AccessInterceptorValueHolderFactory as AccessInterceptor;

	abstract class BaseDecoratorHandler implements ModifyInjected {

		protected $methodHooks = [], $proxyConfig;

		public function __construct (DecoratorProxy $proxyConfig) {

			$this->proxyConfig = $proxyConfig;
		}

		public function getMethodHooks ():array {

			return $this->methodHooks;
		}

		/**
		 * @return Object proxy
		*/
		protected function allMethodAction (object $concrete, callable $action):object {

			foreach ($this->callableMethods($concrete) as $methodName)

				$this->methodHooks[$methodName] = $action;

			return $this->getProxy($concrete);
		}

		protected function callableMethods (object $instance):array {

			$methods = get_class_methods($instance);

			unset($methods[
				array_search(Container::CLASS_CONSTRUCTOR, $methods)
			]);

			return $methods;
		}

		protected function getProxy (object $concrete):object {

			return (new AccessInterceptor(

				$this->proxyConfig->getConfigClient()
			))
			->createProxy( $concrete,

				$this->convertActionsToHook( $this->getMethodHooks())
				// no argument 3 since we don't care about post hooks
			);
		}

		/**
		 * @param {baseActions} [method => function (object $concrete, string $methodName, array $argumentList)]
		*/
		private function convertActionsToHook (array $baseActions):array {

			$hookers = [];

			foreach ($baseActions as $hooker => $action) // handlers with same method won't clash since we're using unique proxies for each handler

				$hookers[$hooker] = function ($proxy, $concrete, $calledMethod, $parameters, &$earlyReturn) use ($action) { // think hooker == calledMethod

					$earlyReturn = true; // since handlers want to take responsibility of calling underlying concrete, not this library

					return call_user_func_array($action, [

						$concrete, $calledMethod, $parameters
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