<?php
	namespace Suphle\Tests\Unit\Files;

	use Suphle\Contracts\IO\Image\{ImageOptimiseOperation, ThumbnailOperationHandler};

	use Suphle\IO\Image\Jobs\AsyncImageProcessor;

	use Suphle\IO\Image\OptimizersManager;

	use Suphle\Queues\AdapterManager;

	use Suphle\Testing\{TestTypes\IsolatedComponentTest, Condiments\QueueInterceptor};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	class AsyncImageProcessorTest extends IsolatedComponentTest {

		use QueueInterceptor, CommonBinds;

		protected $usesRealDecorator = true;

		public function test_task_calls_image_modifier () {

			$this->replaceConstructorArguments(AsyncImageProcessor::class, [

				"operation" => $this->positiveDouble(ImageOptimiseOperation::class, [], [

					"getTransformed" => [1, []] // then
				])
			])->handle(); // when
		}

		public function test_async_operations_are_queued () {

			$operationName = ThumbnailOperationHandler::class;

			$sut = $this->container->whenTypeAny()->needsAny([

				$operationName => $this->positiveDouble($operationName, [// given

					"savesAsync" => true
				])
			])
			->getClass(OptimizersManager::class);
			
			$sut->setImages([], "power_banks")->thumbnail(10, 10)

			->savedImageNames(); // when

			$this->assertPushed(AsyncImageProcessor::class); // then
		}
	}
?>