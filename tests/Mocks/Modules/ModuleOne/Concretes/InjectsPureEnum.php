<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Enums\PureEnum;

	class InjectsPureEnum {

		public function __construct (public readonly PureEnum $pureEnum) {

			//
		}
	}
?>