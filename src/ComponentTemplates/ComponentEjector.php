<?php
	namespace Suphle\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Config\ComponentTemplates;

	class ComponentEjector {

		private $componentList;

		public function __construct (

			private readonly Container $container,

			ComponentTemplates $templateConfig
		) {

			$this->componentList = $templateConfig->getTemplateEntries();
		}

		public function depositFiles (?array $componentsToOverride):bool {

			$hydratedComponents = array_map(
				fn($component) => $this->container->getClass($component),

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