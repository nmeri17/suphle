<?php
	namespace Suphle\Tests\Integration\Templating;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Response\Format\Markup;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Proxies\ExaminesHttpResponse};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Throwable;

	class BladeAdapterTest extends IsolatedComponentTest {

		use CommonBinds, ExaminesHttpResponse;

		protected const MARKUP_NAME = "generic.default",

		HANDLER_NAME = "genericHandler";

		public function test_can_render_data () {

			$hotGirls = ["Joy", "Alexis", "Gloria"]; // given

			$renderer = new Markup(self::HANDLER_NAME, self::MARKUP_NAME);

			$renderer->setRawResponse([ "data" => $hotGirls]);

			$this->container->whenTypeAny()->needsAny([

				BaseRenderer::class => $renderer
			])
			->getClass(BaseRenderer::class)->render(); // when

			$responseAsserter = $this->makeExaminable($renderer);

			foreach ($hotGirls as $expected) { // then

				$responseAsserter->assertSee($expected);

				$responseAsserter->assertSee("<li>$expected</li>", false);
			}
		}
	}
?>