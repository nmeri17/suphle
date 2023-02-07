<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Meta;

	use Suphle\Hydration\Structures\BaseInterfaceCollection;

	use Suphle\Contracts\Config\{ Router, Laravel, Flows};

	use Suphle\Contracts\{Events, Auth\UserContract};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\{RouterMock, LaravelMock, FlowMock};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Events\AssignListeners;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

	class CustomInterfaceCollection extends BaseInterfaceCollection {

		public function getConfigs ():array {

			return array_merge(parent::getConfigs(), [

				Flows::class => FlowMock::class,

				Laravel::class => LaravelMock::class,

				Router::class => RouterMock::class
			]);
		}

		public function simpleBinds ():array {

			return array_merge(parent::simpleBinds(), [

				ModuleOne::class => ModuleApi::class,

				Events::class => AssignListeners::class,

				UserContract::class => EloquentUser::class
			]);
		}
	}
?>