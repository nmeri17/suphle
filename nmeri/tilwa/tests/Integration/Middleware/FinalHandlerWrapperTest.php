<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Middleware\Handlers\FinalHandlerWrapper;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Response\ResponseManager;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	class FinalHandlerWrapperTest extends IsolatedComponentTest {

		public function test_extracts_from_response_manager () {

			$sutName = ResponseManager::class;

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