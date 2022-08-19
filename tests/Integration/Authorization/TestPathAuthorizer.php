<?php
	namespace Suphle\Tests\Integration\Authorization;

	use Suphle\Request\PathAuthorizer;

	use Suphle\Bridge\Laravel\LaravelAppConcrete;

	use Suphle\Adapters\Orms\Eloquent\{OrmLoader, Models\User as EloquentUser};

	use Suphle\Contracts\{Auth\UserContract, Database\OrmBridge};

	use Suphle\Testing\{Proxies\SecureUserAssertions, Condiments\BaseDatabasePopulator};

	use Suphle\Tests\Integration\Routing\TestsRouter;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Auth\AuthorizeRoutes;

	abstract class TestPathAuthorizer extends TestsRouter {

		use SecureUserAssertions, BaseDatabasePopulator;
		
		protected function getEntryCollection ():string {

			return AuthorizeRoutes::class;
		}

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		protected function makeUser (bool $makeAdmin = false):UserContract {

			return $this->replicator->modifyInsertion(1, [

				"is_admin" => $makeAdmin
			])->first();
		}

		// can't move this to setUp since this object is updated after request is updated
		protected function getAuthorizer ():PathAuthorizer {

			return $this->container->getClass(PathAuthorizer::class);
		}

		protected function authorizationSuccess ():bool {

			return $this->getAuthorizer()->passesActiveRules();
		}

		protected function preDatabaseFreeze ():void {

			$this->setUser();
		}

		abstract protected function setUser ():void;
	}
?>