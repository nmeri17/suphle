<?php
	namespace Suphle\Tests\Integration\Auth\Bases;

	use Suphle\Adapters\Orms\Eloquent\Models\User as EloquentUser;

	use Suphle\Contracts\Auth\{ModuleLoginHandler, LoginActions, LoginFlowMediator};

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Response\Format\Json;

	use Suphle\Testing\{ Condiments\BaseDatabasePopulator, Proxies\SecureUserAssertions };

	use Suphle\Tests\Integration\Generic\CommonBinds;

	/**
	 * Used for testing login functionality using the raw collaborators i.e. without http responses, middleware, validation etc
	*/
	trait TestLoginMediator {

		use BaseDatabasePopulator, UserInserter, CommonBinds, SecureUserAssertions {

			CommonBinds::concreteBinds as commonConcretes;
		}

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

		protected function concreteBinds ():array {

			$routerName = RoutedRendererManager::class;

			return array_merge($this->commonConcretes(), [

				$routerName => $this->replaceConstructorArguments($routerName, [], [

					"invokePreviousRenderer" => new Json("") // since we're just sending a post request without an initial get
				])
			]);
		}

		protected function evaluateLoginStatus ():void {

			$this->container->getClass(ModuleLoginHandler::class)

			->setResponseRenderer();
		}

		protected function injectLoginMediator (int $successCount, int $failureCount):void {

			$localLoginManager = $this->replaceConstructorArguments(

				$this->loginRendererName(), [

					"authService" => $this->container->getClass($this->loginRepoService()) // injecting this since PHPUnit won't recursively hydrate dependencies and we need to evaluate the "comparer" property
				], [], [

					"successRenderer" => [$successCount, []],

					"failedRenderer" => [$failureCount, []]
				]
			);

			$this->container->whenTypeAny()->needsAny([

				$this->loginRendererName() => $localLoginManager
			]);
		}
	}
?>