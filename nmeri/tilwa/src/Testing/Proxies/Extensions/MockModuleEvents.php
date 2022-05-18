<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Events\{ModuleLevelEvents, EventSubscription};

	use Tilwa\Contracts\Config\Events;

	use Tilwa\Hydration\Container;

	class MockModuleEvents extends ModuleLevelEvents {

		protected function moduleHasListeners (Events $config, Container $container):void {

			$container->whenTypeAny()->needsAny([

				ModuleLevelEvents::class => $this // provide self since managers import ModuleLevelEvents
			]);

			parent::moduleHasListeners($config, $container);
		}
	}
?>