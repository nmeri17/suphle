<?php
	namespace Suphle\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Config\ComponentTemplates;

	class ComponentEjector {

		private $componentList, $container;

		public function __construct (Container $container, ComponentTemplates $templateConfig) {

			$this->container = $container;

			$this->componentList = $templateConfig->getTemplateEntries();
		}

		// remember to check for duplicates/existing before replacing
		// must prefix
		public function depositFiles (?array $componentsToOverride):bool {

			$hydratedComponents = array_map(function ($component) {

				return $this->container->getClass($component);
			}, $this->componentList);

			foreach ($hydratedComponents as $component) {

				if (
					!$component->hasBeenEjected() ||

					$this->shouldOverride($component, $componentsToOverride)
				)

					$component->eject(); // the prefixing happens here
			}
		}

		protected function shouldOverride (BaseComponentEntry $component, ?array $componentsToOverride):bool {

			if (is_null($componentsToOverride)) return true;

			return in_array(get_class($component), $componentsToOverride);
		}
	}
?>