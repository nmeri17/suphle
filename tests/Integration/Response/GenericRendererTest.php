<?php
	namespace Suphle\Tests\Integration\Response;

	use Suphle\Response\Format\Json;

	use Suphle\Testing\TestTypes\IsolatedComponentTest;

	use Suphle\Tests\Integration\Generic\CommonBinds;

	use Illuminate\Support\Collection;

	class GenericRendererTest extends IsolatedComponentTest {

		use CommonBinds;

		/**
		 * @dataProvider existingResponses
		*/
		public function test_can_merge_content_into_renderer_body ($response, array $expectedShape) {

			$newMessage = ["message" => "extra data"];

			$renderer = (new Json(""))->setRawResponse($response); // given

			$renderer->forceArrayShape($newMessage); // when

			$this->assertSame(

				$renderer->getRawResponse(),

				array_merge($expectedShape, $newMessage)
			); // then
		}

		public function existingResponses ():array {

			return [

				[range(1, 5), range(1, 5)],

				[new Collection, []]
			];
		}
	}
?>