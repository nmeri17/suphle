<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleThree\Config;

	use Suphle\Config\DefaultFlowConfig;

	class FlowMock extends DefaultFlowConfig {

		/**
		 * {@inheritdoc}
		*/
		public function isEnabled ():bool {

			return true;
		}
	}
?>