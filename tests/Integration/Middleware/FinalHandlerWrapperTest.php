<?php
	namespace Suphle\Tests\Integration\Middleware;

	use Suphle\Middleware\Handlers\FinalHandlerWrapper;

	use Suphle\Request\PayloadStorage;

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Contracts\Response\RendererManager;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	class FinalHandlerWrapperTest extends IsolatedComponentTest {

		use CommonBinds;

		public function test_extracts_from_response_manager () {

			$mockManager = $this->positiveDouble(RoutedRendererManager::class, [], [ // then

				"handleValidRequest" => [1, [$this->callback(fn($subject) => $subject instanceof PayloadStorage)]],

				"afterRender" => [1, []],

				"responseRenderer" => [1, []]
			]);

			$this->container->whenTypeAny()->needsAny([

				RendererManager::class => $mockManager
			]); // given

			$this->container->getClass(FinalHandlerWrapper::class)

			->process(
				$this->positiveDouble(PayloadStorage::class),
				null
			); // when
		}
	}
?>