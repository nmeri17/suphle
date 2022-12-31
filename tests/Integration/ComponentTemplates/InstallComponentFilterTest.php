<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\ComponentTemplates\{ComponentEjector, BaseComponentEntry};

	use Suphle\Exception\ComponentEntry as ExceptionComponentEntry;

	use Suphle\Contracts\Config\ComponentTemplates;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use PHPUnit\Framework\MockObject\MockObject;

	class InstallComponentFilterTest extends IsolatedComponentTest {

		use CommonBinds;

		protected array $templateEntries = [];

		protected string $ejectorName = ComponentEjector::class;

		public function test_can_override_existing__all () {

			// then
			$this->addTemplateEntry(true, 1);

			$this->addTemplateEntry(false, 1);

			$this->replaceConstructorArguments($this->ejectorName, [

				"templateConfig" => $this->getTemplateConfig() // given
			])
			// when
			->depositFiles([]); // called with just the option
		}

		protected function addTemplateEntry (bool $hasEjected, int $willEject):string {

			$stubMethods = [

				"hasBeenEjected" => $hasEjected
			];

			$mockMethods = [

				"eject" => [$willEject, []]
			];

			$entry = $this->doubleAbstractEntry($stubMethods, $mockMethods);

			$this->stubSingle($stubMethods, $entry);

			$this->mockCalls($mockMethods, $entry);

			$this->container->whenTypeAny()->needsAny([

				$entry::class => $entry
			]);

			$this->templateEntries[] = $entry;

			return $entry::class;
		}

		protected function doubleAbstractEntry (array $stubMethods, array $mockMethods):MockObject {

			$sutName = BaseComponentEntry::class;

			$builder = $this->getMockBuilder($sutName);

			$parameters = $this->container->getMethodParameters(Container::CLASS_CONSTRUCTOR, $sutName);

			$builder->setConstructorArgs($parameters);

			$builder->onlyMethods([...array_keys($stubMethods), ...array_keys($mockMethods)]);

			$builder->disableArgumentCloning()

			->setMockClassName("ComponentEntry". count($this->templateEntries)); // without this, phpunit returns same name for all doubles extending this classes. When it gets bound to the container or lifted using autoload, the first created double gets overwritten

			return $builder->getMockForAbstractClass();
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
			->depositFiles(null); // called without the option
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