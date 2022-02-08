<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Middleware\FinalHandlerWrapper;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Response\ResponseManager;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Prophecy\Argument;

	class FinalHandlerWrapperTest extends IsolatedComponentTest {

		public function test_extracts_from_response_manager () {

			$sutName = ResponseManager::class;

			$prophet = $this->getProphet();

			$mockManager = $prophet->prophesize($sutName);

			// then
			$mockManager->handleValidRequest(Argument::type(PayloadStorage::class))->shouldBeCalled();

			$mockManager->afterRender()->shouldBeCalled();

			$mockManager->getResponse()->shouldBeCalled();

			$this->container->whenTypeAny()->needsAny([

				$sutName => $mockManager->reveal()
			]); // given

			$this->container->getClass(FinalHandlerWrapper::class)

			->process(
				$prophet->prophesize(PayloadStorage::class)->reveal(),
				null
			); // when
		}
	}
?>