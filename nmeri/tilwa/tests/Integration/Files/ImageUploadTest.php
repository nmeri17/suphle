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

		protected $debugCaughtExceptions = true;

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

			$response = $this->sendUploadRequest("/apply-all");

			$this->assertSavedFiles(["*.*"], $response);
		}

		private function sendUploadRequest (string $url):TestResponseBridge {

			return $this->postJson("/api/v1/$url", [

					"belonging_resource" => $this->resourceOwner

				], [], [

				"profile_pic" => (new FileFactory) // since file reading is generic, we aren't strictly concerned about key names. That should be enforced through the validator. We just lift anything that comes there since it doesn't make sense to post files without saving

					->create("portait.png", 300)
				]
			);
		}

		public function test_saves_with_correct_name_format () {

			$operation = "thumbnail";

			$imagePath = $this->sendUploadRequest("/apply-crop")

			->getContent()[$operation][0];

			$pattern = $this->resourceOwner ."\/$operation\/\w+\.";

			$this->assertTrue(preg_match("/$pattern/", $imagePath));
		}

		// test async version calls expected method
	}
?>