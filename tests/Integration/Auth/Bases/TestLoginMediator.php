<?php
	namespace Suphle\Tests\Integration\Auth\Bases;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	use Suphle\Contracts\Auth\{ModuleLoginHandler, LoginActions, LoginFlowMediator};

	use Suphle\Hydration\Container;

	use Suphle\Testing\{ Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions };

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	/**
	 * Used for testing login functionality using the raw collaborators i.e. without http responses, middleware, validation etc
	*/
	trait TestLoginMediator {

		use BaseDatabasePopulator, UserInserter, SecureUserAssertions;

		/**
		 * These are used to determine whether login passed or failed
		 * 
		 * @return class-string LoginFlowMediator
		*/
		abstract protected function loginRendererName ():string;

		/**
		 * @return class-string LoginActions
		*/
		abstract protected function loginRepoService ():string;

		protected function getActiveEntity ():string {

			return EloquentUser::class;
		}

		protected function getModules ():array {

			return [
				new ModuleOneDescriptor (new Container)
			];
		}

		protected function evaluateLoginStatus ():void {

			$this->getContainer()->getClass(ModuleLoginHandler::class)

			->setResponseRenderer();
		}

		protected function bindAuthStatusObserver (int $successCount, int $failureCount):void {

			$localLoginManager = $this->replaceConstructorArguments(

				$this->loginRendererName(), [

					"authService" => $this->getContainer()->getClass($this->loginRepoService()) // injecting this since PHPUnit won't recursively hydrate dependencies and we need to evaluate the "comparer" property
				], [], [

					"successRenderer" => [$successCount, []],

					"failedRenderer" => [$failureCount, []]
				]
			);

			$this->massProvide([

				$this->loginRendererName() => $localLoginManager
			]);
		}
	}
?>