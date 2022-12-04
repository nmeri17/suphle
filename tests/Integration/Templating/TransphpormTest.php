<?php
	namespace Suphle\Tests\Integration\Templating;

	use Suphle\Contracts\{Database\OrmDialect, Presentation\TransphpormRenderer};

	use Suphle\Contracts\Config\{Transphporm as ViewConfig, ModuleFiles};

	use Suphle\Hydration\Container;

	use Suphle\Adapters\Markups\Transphporm;

	use Suphle\Response\Format\Markup;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Proxies\ExaminesHttpResponse};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Throwable;

	class TransphpormTest extends IsolatedComponentTest {

		use CommonBinds, ExaminesHttpResponse;

		protected const MARKUP_NAME = "profile";
  protected const HANDLER_NAME = "genericHandler";

		private string $sutName = Transphporm::class;

		public function test_will_infer_template_name () {

			$renderer = (new Markup(self::HANDLER_NAME, self::MARKUP_NAME, null))

			->setConfigs(
				$this->negativeDouble(ModuleFiles::class, []),
				
				$this->positiveDouble(ViewConfig::class, [

					"inferFromViewName" => true // given
				])
			);

			$this->assertSame(

				self::MARKUP_NAME, $renderer->safeGetTemplateName() // when
			); // then
		}

		public function test_cant_if_infer_and_template_are_off () {

			$this->expectException(Throwable::class); // then

			$renderer = (new Markup(self::HANDLER_NAME, self::MARKUP_NAME, null))

			->setConfigs(
				$this->negativeDouble(ModuleFiles::class, []),
				
				$this->positiveDouble(ViewConfig::class, [

					"inferFromViewName" => false // given
				])
			);

			$renderer->safeGetTemplateName(); // when
		}

		public function test_can_render_data () {

			// given
			$message = "Joy, Alexis, and Gloria";

			$markupName = "generic/default";

			$anchorPath = $this->container->getClass(ModuleFiles::class)

			->defaultViewPath();

			$templatePath = $this->container->getClass(ViewConfig::class)

			->getTssPath();

			$renderer = $this->positiveDouble(TransphpormRenderer::class, [

				"getMarkupPath" => $anchorPath . $markupName,

				"getTemplatePath" => $templatePath . $markupName,

				"getRawResponse" => compact("message")
			]);

			$result = $this->container->getClass($this->sutName)

			->parseAll($renderer); // when

			$this->assertStringContainsString($message, $result);
		}
	}
?>