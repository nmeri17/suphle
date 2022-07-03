<?php
	namespace Tilwa\Tests\Integration\Files;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Exception\Explosives\Generic\UnmodifiedImageException;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\FilesystemCleaner};

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, Extensions\TestResponseBridge};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\ImageUploadCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	use Illuminate\Http\Testing\FileFactory;

	class ImageUploadTest extends ModuleLevelTest {

		use FilesystemCleaner;

		private $resourceOwner = "users";

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
			foreach (["thumbnail", "inferior"] as $operation)

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

			$imagePath = $decoded[$operation]["profile_pic"]; // spits given array back but with names instead of files

			$pattern = $this->resourceOwner . "\\\\$operation\\\\".

			"\w+\.";

			$matchResult = preg_match("/$pattern/", $imagePath);

			$this->assertSame(1, $matchResult); // then
		}

		private function getDecodedResponse (string $url):array {

			$fullResponse = $this->sendUploadRequest($url);

			return json_decode($fullResponse->getContent(), true);
		}
	}
?>