<?php
	namespace Tilwa\Tests\Integration\Templating;

	use Tilwa\Adapters\Markups\Transphporm;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class TransphpormTest extends ModuleLevelTest {

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_can_infer_view_model_name () {

			$sutName = Transphporm::class;

			$markupName = "profile";

			$parameters = $this->getContainer()->getMethodParameters($sutName);

			$this->replaceConstructorArguments($sutName, $parameters, [], [

				"readFile" => [1, [$this->callback(function ($subject) use ($markupName) {

					return str_contains($subject, $markupName);
				})]]
			])
			->parseAll($markupName, null, []);
		}

		public function test_can_render_data () {

			// use assertSee
		}
	}
?>