<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\ComponentTemplates\{ComponentEjector, BaseComponentEntry};

	use Suphle\Exception\ComponentEntry as ExceptionComponentEntry;

	use Suphle\Contracts\Config\ComponentTemplates;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	class InstallComponentFilterTest extends IsolatedComponentTest {

		use CommonBinds;

		private $templateEntries = [],

		$ejectorName = ComponentEjector::class;

		public function test_can_override_existing__all () {

			// then
			$this->addTemplateEntry(true, 1);

			$this->addTemplateEntry(false, 1);

			$this->replaceConstructorArguments($this->ejectorName, [

				"templateConfig" => $this->getTemplateConfig() // given
			])
			// when
			->depositFiles(null); // called with just the option
		}

		protected function addTemplateEntry (bool $hasEjected, int $willEject):string {

			$entry = $this->positiveDouble(BaseComponentEntry::class, [

				"hasBeenEjected" => $hasEjected
			], [

				"eject" => [$willEject, []]
			]);

			$this->container->whenTypeAny()->needsAny([

				get_class($entry) => $entry
			]);

			$this->templateEntries[] = $entry;

			return get_class($entry);
		}

		protected function getTemplateConfig ():ComponentTemplates {

			return $this->positiveDouble(ComponentTemplates::class, [

				"getTemplateEntries" => array_map("get_class", $this->templateEntries)
			]);
		}

		public function test_will_override_only_non_ejected () {

			// then
			$this->addTemplateEntry(true, 0);

			$this->addTemplateEntry(false, 1);

			$this->replaceConstructorArguments($this->ejectorName, [

				"templateConfig" => $this->getTemplateConfig() // given
			])
			// when
			->depositFiles([]); // called without the option
		}

		public function test_can_override_existing__some () {

			$toClear = [];

			// then
			$toClear[] = $this->addTemplateEntry(true, 1);

			$this->addTemplateEntry(true, 0);

			$toClear[] = $this->addTemplateEntry(true, 1);

			$this->replaceConstructorArguments($this->ejectorName, [

				"templateConfig" => $this->getTemplateConfig() // given
			])
			// when
			->depositFiles($toClear);
		}
	}
?>