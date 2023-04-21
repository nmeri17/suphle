<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Markup\Components;

	use Illuminate\View\Component;

	class AppLayout extends Component {

		public function __construct ($pageTitle, $scripts = null) {

			//
		}

		public function render () {

			return view("layouts.app");
		}
	}
?>