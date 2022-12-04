<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes;

	use Suphle\Events\EmitProxy;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleApi, Events\AssignListeners};

	class LocalSender {

		use EmitProxy;

		final const CASCADE_BEGIN_EVENT = "cascading";
  final const CONCAT_EVENT = "concating";
  final const CASCADE_EXTERNAL_BEGIN_EVENT = "begin_external_cascade";
  final const EMPTY_PAYLOAD_EVENT = "no_payload";

		public function __construct (AssignListeners $eventManager) {

			$this->eventManager = $eventManager;
		}

		public function sendLocalEvent ($payload):void {

			$this->emitHelper (ModuleApi::DEFAULT_EVENT, $payload);
		}

		public function sendLocalEventNoPayload ():void {

			$this->emitHelper (ModuleApi::EMPTY_PAYLOAD_EVENT);
		}

		public function cascadingEntry ($payload):void {

			$this->emitHelper (self::CASCADE_BEGIN_EVENT, $payload);
		}

		public function sendConcatHalf ($payload):void {

			$this->emitHelper (self::CONCAT_EVENT, $payload);
		}

		public function beginExternalCascade ($payload):void {

			$this->emitHelper (self::CASCADE_EXTERNAL_BEGIN_EVENT, $payload);
		}
	}
?>