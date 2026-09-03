<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Services;

use Suphle\Events\{EmitProxy, EventPropagator};

use Suphle\Services\Attributes\DomainService;

#[DomainService(mutation: true)]
class EmitterService {
	use EmitProxy;

	public const EMPTY_PAYLOAD_EVENT = "empty_payload";
	public const CASCADE_BEGIN_EVENT = "cascading";
	public const CONCAT_EVENT = "concat_event";
	public const CASCADE_EXTERNAL_BEGIN_EVENT = "cascade_external_begin";

	public function __construct(protected readonly EventPropagator $eventEmitter)
	{ }

	public function triggerEmptyPayload(): void
	{
		$this->emitHelper(self::EMPTY_PAYLOAD_EVENT);
	}

	public function triggerCascadeBegin($payload): void
	{
		$this->emitHelper(self::CASCADE_BEGIN_EVENT, $payload);
	}

	public function triggerConcat($payload): void
	{
		$this->emitHelper(self::CONCAT_EVENT, $payload);
	}

	public function triggerCascadeExternalBegin($payload): void
	{
		$this->emitHelper(self::CASCADE_EXTERNAL_BEGIN_EVENT, $payload);
	}
}