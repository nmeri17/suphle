<?php
	namespace Suphle\Tests\Integration\Files;

	use Suphle\Contracts\Config\Router;

	use Suphle\Contracts\IO\Image\{InferiorOperationHandler, ThumbnailOperationHandler};

	use Suphle\Exception\Explosives\Generic\UnmodifiedImageException;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\FilesystemCleaner};

	use Suphle\Testing\Proxies\{WriteOnlyContainer, Extensions\TestResponseBridge};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\ImageUploadCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	class ImageUploadTest extends ModuleLevelTest {

		use FilesystemCleaner;

		private string $resourceOwner = "users";

		protected bool $debugCaughtExceptions = true; // it's important to leave this in, otherwise the test, test_giving_no_operation_throws_error, will swallow error, causing test to "fail"

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => ImageUploadCollection::class
					]);
				})
			];
		}

		public function test_giving_no_operation_throws_error () {

			$this->expectException(UnmodifiedImageException::class); // then

			$this->sendUploadRequest("/apply-none"); // when
		}

		public function test_can_save_multiple_operations () {

			$response = $this->getDecodedResponse("/apply-all"); // when

			// then
			foreach ([

				InferiorOperationHandler::OPERATION_NAME,

				ThumbnailOperationHandler::OPERATION_NAME
			] as $operation)

				$this->assertArrayHasKey($operation, $response);

			$this->assertSavedFiles(["*.*"], $response);
		}

		private function sendUploadRequest (string $url):TestResponseBridge {

			return $this->postJson("/api/v1/$url", [ // using mirroring to bypass csrf errors

					"belonging_resource" => $this->resourceOwner

				], [], [

					"profile_pic" => $this->saveFakeImage("portait.png", 450, 200, 300)
				] // since file reading is generic, we aren't strictly concerned about key names. That should be enforced through the validator. We just lift anything that comes there since it doesn't make sense to post files without saving
			);
		}

		public function test_saves_with_correct_name_format () {

			$operation = "thumbnail";

			$decoded = $this->getDecodedResponse("/apply-crop"); // when

			$this->assertArrayHasKey($operation, $decoded);

			$imagePath = $decoded[$operation]["profile_pic"]; // spits given array back but with names instead of files

			$pattern = $this->resourceOwner . "\\\\$operation\\\\".

			"\w+\.";

			$matchResult = preg_match("/$pattern/", (string) $imagePath);

			$this->assertSame(1, $matchResult); // then
		}

		private function getDecodedResponse (string $url):array {

			$fullResponse = $this->sendUploadRequest($url);

			return json_decode((string) $fullResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
		}
	}
?>