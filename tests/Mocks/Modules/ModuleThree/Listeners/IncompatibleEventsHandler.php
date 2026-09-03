<?php
namespace Suphle\Tests\Mocks\Modules\ModuleThree\Listeners;

use Suphle\Events\Attributes\{HasHandlers, EventListener};

use Suphle\Tests\Mocks\Interactions\ModuleOne;

#[HasHandlers(ModuleOne::class)]
class IncompatibleEventsHandler
{
	private $payload;

	#[EventListener(ModuleOne::DEFAULT_EVENT)]
	public function handleImpossibleEmit(int $payload)
	{

		$this->payload = $payload;
	}
}
