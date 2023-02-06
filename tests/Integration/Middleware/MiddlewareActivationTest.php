<?php
	namespace Suphle\Tests\Integration\Middleware;

	use Suphle\Contracts\Config\Router;

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Testing\{ TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator };

	use Suphle\Testing\Proxies\{ WriteOnlyContainer, SecureUserAssertions };

	use Suphle\Tests\Integration\Middleware\Helpers\MocksMiddleware;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{ BlankMiddleware, BlankMiddleware2};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\{ActualEntry, Secured\MisleadingEntry};

	class MiddlewareActivationTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions, MocksMiddleware {

			BaseDatabasePopulator::setUp as databaseAllSetup;
		}

		private string $threeTierUrl = "/first/middle/without";

		private $contentVisitor;

		protected function setUp ():void {

			$this->databaseAllSetup();

			$this->contentVisitor = $this->replicator->getRandomEntity();
		}

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => MisleadingEntry::class
					]);
				})
			];
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		public function test_can_activate_middleware () {

			$this->actingAs($this->contentVisitor);

			$middlewareName = BlankMiddleware2::class;

			$expectedMiddleware = [$middlewareName];

			$this->withMiddleware($expectedMiddleware); // given

			$this->provideMiddleware([ // then 1

				$middlewareName => $this->getMiddlewareMock($middlewareName, 1)
			]);

			$this->get($this->threeTierUrl) // when

			->assertOk(); // sanity checks

			$this->assertUsedMiddleware($expectedMiddleware); // then 2
		}

		public function test_can_deactivate_middleware () {

			$this->actingAs($this->contentVisitor);

			$middlewareName = BlankMiddleware::class;

			$expectedMiddleware = [$middlewareName]; // can actually be found at the route

			$this->withoutMiddleware($expectedMiddleware); // given

			$this->provideMiddleware([ // then 1

				$middlewareName => $this->getMiddlewareMock($middlewareName, 0)
			]);

			$this->get($this->threeTierUrl) // when

			->assertOk(); // sanity checks

			$this->assertDidntUseMiddleware($expectedMiddleware); // then 2
		}
	}
?>