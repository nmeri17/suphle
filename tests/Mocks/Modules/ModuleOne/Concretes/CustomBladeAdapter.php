<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Suphle\Adapters\Presentation\Blade\DefaultBladeAdapter;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Markup\Components\AppLayout;

	class CustomBladeAdapter extends DefaultBladeAdapter {
		
		public function bindComponentTags ():void {
			
			$this->bladeCompiler->component("layout", AppLayout::class);
		}
	}
?>