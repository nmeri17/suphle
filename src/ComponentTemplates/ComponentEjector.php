<?php
	namespace Suphle\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Config\ComponentTemplates;

	class ComponentEjector {

		protected readonly array $componentList;

		public function __construct (

			protected readonly Container $container,

			ComponentTemplates $templateConfig
		) {

			$this->componentList = $templateConfig->getTemplateEntries();
		}

		public function depositFiles (?array $componentsToOverride, array $componentArguments):bool {

			$hydratedComponents = array_map(
				function ($component) use ($componentArguments) {

					$instance = $this->container->getClass($component);

					$instance->setInputArguments($componentArguments);

					return $instance;
				},

				$this->componentList
			);

			foreach ($hydratedComponents as $component) {

				if (
					!$component->hasBeenEjected() ||

					$this->shouldOverride($component, $componentsToOverride)
				)

					$component->eject();
			}

			return true;
		}

		protected function shouldOverride (BaseComponentEntry $component, ?array $componentsToOverride):bool {

			if (is_null($componentsToOverride)) return false;

			if (empty($componentsToOverride)) return true;

			return in_array($component::class, $componentsToOverride);
		}
	}
?>