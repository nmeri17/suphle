<?php
	namespace Suphle\Adapters\Presentation\Blade;

	use Suphle\Hydration\BaseInterfaceLoader;

	class BladeInterfaceLoader extends BaseInterfaceLoader {

		public function concreteName ():string {

			return DefaultBladeAdapter::class;
		}

		public function afterBind ($initialized):void {

			$initialized->setViewFactory();

			$initialized->bindComponentTags();
		}
	}
?>