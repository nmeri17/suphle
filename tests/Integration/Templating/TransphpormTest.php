<?php
	namespace Suphle\Tests\Integration\Templating;

	use Suphle\Contracts\Database\OrmDialect;

	use Suphle\Hydration\Container;

	use Suphle\Adapters\Markups\Transphporm;

	use Suphle\Response\Format\Markup;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Proxies\ExaminesHttpResponse};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	class TransphpormTest extends IsolatedComponentTest {

		use CommonBinds, ExaminesHttpResponse;

		private $sutName = Transphporm::class;

		public function test_can_infer_view_model_name () {

			$markupName = "profile";

			$parameters = $this->container->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, $this->sutName
			);

			$this->replaceConstructorArguments($this->sutName, $parameters, [], [

				"readFile" => [2, [$this->callback(function ($subject) use ($markupName) { // then

					return str_contains($subject, $markupName);
				})]]
			])
			->parseAll($markupName, null, []); // when
		}

		public function test_can_render_data () {

			// given
			$message = "Joy, Alexis, and Gloria";

			$viewName = "generic/default";

			$result = $this->container->getClass($this->sutName)

			->parseAll($viewName, $viewName, compact("message")); // when

			$this->container->getClass(OrmDialect::class); // their examiner requires a helper to convert markup responses to a special, testable type. So, we use this instead of directly requiring helper file

			$this->makeExaminable($this->makeRenderer($result))

			->assertSee($message); // then
		}

		private function makeRenderer (string $content):Markup {

			return (new Markup("genericHandler", "viewName"))

			->setRawResponse($content);
		}
	}
?>