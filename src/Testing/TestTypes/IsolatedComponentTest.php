<?php
	namespace Suphle\Testing\TestTypes;

	use Suphle\Contracts\IO\{Session, CacheManager};

	use Suphle\Contracts\Queues\Adapter as QueueAdapter;

	use Suphle\Hydration\Container;

	use Suphle\Adapters\{Session\InMemorySession, Cache\InMemoryCache};

	use Suphle\Request\RequestDetails;

	use Suphle\Testing\Proxies\{ConfigureExceptionBridge, ExceptionBroadcasters};

	/**
	 * Used for tests that mostly require a Container. Boots and provides this container to them
	*/
	abstract class IsolatedComponentTest extends TestVirginContainer {

		use ConfigureExceptionBridge, ExceptionBroadcasters {

			ConfigureExceptionBridge::setUp as mufflerSetup;
		}

		protected Container $container;

		protected bool $usesRealDecorator = true;

		protected function setUp ():void {

			$this->container = $container = $this->positiveDouble(

				Container::class,

				$this->getContainerStubs()
			);

			$this->bootContainer($container);

			$this->withDefaultInterfaceCollection($container);

			$this->entityBindings();

			$this->maySetRealDecorator();

			$this->mayMonitorContainer($this->container);

			$this->mufflerSetup();
		}

		protected function entityBindings ():void {

			foreach ($this->concreteBinds() as $name => $concrete) // this goes first so if any of the simpleBinds below requires a concrete, it'll be available to it

				$this->container->whenTypeAny()->needsAny([

					$name => $concrete
				]);

			foreach ($this->simpleBinds() as $contract => $className) {

				$concrete = $this->container->getClass($className); // not safe to hydrate entity within a provision

				$this->container->whenTypeAny()->needsAny([

					$contract => $concrete
				]);
			}
		}

		protected function simpleBinds ():array {

			return [

				Session::class => InMemorySession::class,

				CacheManager::class => InMemoryCache::class
			];
		}

		protected function concreteBinds ():array {

			return array_merge($this->getExceptionDoubles(), [

				QueueAdapter::class => $this->positiveDouble(QueueAdapter::class)
			]);
		}

		// used for normalizing traits that are applicable to both this and module level test
		protected function getContainer ():Container {

			return $this->container;
		}

		protected function massProvide (array $provisions):void {

			$this->container->refreshMany(array_keys($provisions));

			$this->container->whenTypeAny()->needsAny($provisions);
		}

		private function getContainerStubs ():array {

			$stubs = [];

			if (!$this->usesRealDecorator)

				$stubs["getDecorator"] = $this->stubDecorator();

			return $stubs;
		}

		private function maySetRealDecorator ():void {

			if ($this->usesRealDecorator)

				$this->container->interiorDecorate();
		}

		protected function setRequestPath (string $requestPath):void {

			RequestDetails::fromContainer($this->container, $requestPath);
		}
	}
?>