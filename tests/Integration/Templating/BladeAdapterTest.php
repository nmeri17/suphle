<?php
	namespace Suphle\Tests\Integration\Templating;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Response\Format\Markup;

	use Suphle\Hydration\Container;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\ExaminesHttpResponse};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Throwable;

	class BladeAdapterTest extends ModuleLevelTest { // required cuz blade uses laravel container which triggers events

		use ExaminesHttpResponse;

		protected const MARKUP_NAME = "generic.default",

		HANDLER_NAME = "genericHandler";

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
		}

		public function test_can_render_data () {

			$hotGirls = ["Joy", "Alexis", "Gloria"]; // given

			$renderer = new Markup(self::HANDLER_NAME, self::MARKUP_NAME);

			$renderer->setRawResponse([ "data" => $hotGirls]);

			$this->getContainer()->whenTypeAny()->needsAny([

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