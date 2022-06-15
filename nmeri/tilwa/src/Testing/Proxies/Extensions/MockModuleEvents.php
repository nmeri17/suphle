<?php
	namespace Tilwa\Testing\Proxies\Extensions;

	use Tilwa\Events\{ModuleLevelEvents, EventSubscription};

	use Tilwa\Contracts\{Config\Events, Modules\DescriptorInterface};

	use Tilwa\Hydration\Container;

	class MockModuleEvents extends ModuleLevelEvents {

		protected function moduleHasListeners (Events $config, DescriptorInterface $descriptor, Container $container):void {

			$container->whenTypeAny()->needsAny([

				ModuleLevelEvents::class => $this // provide self since managers import ModuleLevelEvents
			]);

			parent::moduleHasListeners($config, $descriptor, $container);
		}
	}
?>