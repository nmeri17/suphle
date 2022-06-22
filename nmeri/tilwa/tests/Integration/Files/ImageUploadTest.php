<?php
	namespace Tilwa\Tests\Integration\Files;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Exception\Explosives\Generic\UnmodifiedImageException;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\FilesystemCleaner};

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, Extensions\TestResponseBridge};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\ImageUploadCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	use Illuminate\Http\Testing\FileFactory;

	class ImageUploadTest extends ModuleLevelTest {

		use FilesystemCleaner;

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

			$this->sendUploadRequest("/apply-all");
		}

		private function sendUploadRequest (string $url):TestResponseBridge {

			return $this->post($url, [

					"belonging_resource" => "users"

				], [], [

				"profile_pic" => (new FileFactory)

					->create("portait.png", 300)
				]
			);
		}

		public function test_can_save_single_operation () {

			// can save one

				// to setResourceName/setName
		}

		// can save all

		// test async version calls expected method
	}
?>