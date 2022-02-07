<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Meta;

	use Tilwa\Contracts\Services\Decorators\MultiUserModelEdit;

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{LocalSender, BCounter, SenderExtension };

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{MultiUserEditMock, UpdatefulEmitter};

	class ModuleApi implements ModuleOne {

		private $localSender, $bCounter, $localSenderExtended,

		$editService, // we're injecting a concrete rather than the interface here since this class is used a lot, and we don't wanna provide that concrete each time

		$errorEditService;

		public function __construct (LocalSender $localSender, BCounter $bCounter, SenderExtension $senderExtension, MultiUserEditMock $editService, UpdatefulEmitter $errorEditService) {

			$this->localSender = $localSender;

			$this->bCounter = $bCounter;

			$this->localSenderExtended = $senderExtension;

			$this->editService = $editService;

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

		public function getResourceEditor ():MultiUserModelEdit {

			$this->editService->getResource();

			return $this->editService;
		}

		public function systemUpdateErrorEvent (int $payload):OptionalDTO {

			$this->errorEditService->initializeUpdateModels($payload);

			return $this->errorEditService->updateModels();
		}
	}
?>