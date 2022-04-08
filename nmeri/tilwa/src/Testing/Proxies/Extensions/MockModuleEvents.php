<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Events\{ModuleLevelEvents, EventSubscription};

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Hydration\Container;

	class MockModuleEvents extends ModuleLevelEvents {

		private $blanks = [];

		public function triggerHandlers(?EventSubscription $scope, string $eventName, $payload):ModuleLevelEvents {

			$this->blanks[] = $scope;

			return $this;
		}

		public function getBlanks ():array {

			return $this->blanks;
		}

		protected function moduleHasListeners (Events $config, Container $container):void {

			$container->whenTypeAny()->needsAny([

				ModuleLevelEvents::class => $this // provide self for the call below
			]);

			parent::moduleHasListeners($config, $container);
		}
	}
?>