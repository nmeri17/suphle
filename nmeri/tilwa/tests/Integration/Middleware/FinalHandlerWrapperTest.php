<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Middleware\Handlers\FinalHandlerWrapper;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	class FinalHandlerWrapperTest extends IsolatedComponentTest {

		use CommonBinds;

		public function test_extracts_from_response_manager () {

			$sutName = RoutedRendererManager::class;

			$mockManager = $this->positiveDouble($sutName, [], [ // then

				"handleValidRequest" => [1, [$this->callback(function($subject) {

					return $subject instanceof PayloadStorage;
				})]],

				"afterRender" => [1, []],

				"getResponse" => [1, []]
			]);

			$this->container->whenTypeAny()->needsAny([

				$sutName => $mockManager
			]); // given

			$this->container->getClass(FinalHandlerWrapper::class)

			->process(
				$this->positiveDouble(PayloadStorage::class),
				null
			); // when
		}
	}
?>