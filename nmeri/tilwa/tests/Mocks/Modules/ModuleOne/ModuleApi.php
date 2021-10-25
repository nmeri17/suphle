<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Events\LocalReceiver, Concretes\LocalSender};

	class ModuleApi implements ModuleOne {

		private $localSender, $localReceiver;

		public function __construct (LocalReceiver $localReceiver, LocalSender $localSender) {

			$this->localSender = $localSender;

			$this->localReceiver = $localReceiver;
		}

		public function getLocalSender ():LocalSender {

			return $this->localReceiver;
		}

		public function getLocalReceiver ():LocalReceiver {

			return $this->localReceiver;
		}
	}
?>