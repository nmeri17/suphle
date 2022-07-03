<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{LocalSender, BCounter, SenderExtension };

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{MultiUserEditMock, UpdatefulEmitter};

	class ModuleApi implements ModuleOne {

		private $localSender, $bCounter, $localSenderExtended,

		$errorEditService;

		public function __construct (LocalSender $localSender, BCounter $bCounter, SenderExtension $senderExtension, UpdatefulEmitter $errorEditService) {

			$this->localSender = $localSender;

			$this->bCounter = $bCounter;

			$this->localSenderExtended = $senderExtension;

			$this->errorEditService = $errorEditService;
		}

		public function setBCounterValue (int $newCount):void {

			$this->bCounter->setCount($newCount);
		}

		public function getBCounterValue ():int {

			return $this->bCounter->getCount();
		}

		public function noPayloadEvent ():void {

			$this->localSender->sendLocalEventNoPayload();
		}

		public function payloadEvent (int $value):void {

			$this->localSender->sendLocalEvent($value);
		}

		public function cascadeEntryEvent (int $value):void {

			$this->localSender->cascadingEntry($value);
		}

		public function sendConcatEvents (int $value):void {

			$this->localSender->sendConcatHalf($value);

			$this->localSender->sendLocalEventNoPayload();
		}

		public function sendExtendedEvent (int $value):void {

			$this->localSenderExtended->sendLocalEvent($value);
		}

		public function multiModuleCascadeEvent (bool $value):void {

			$this->localSender->beginExternalCascade($value);
		}

		public function systemUpdateErrorEvent (int $payload):int {

			$this->errorEditService->initializeUpdateModels($payload);

			return $this->errorEditService->updateModels();
		}
	}
?>