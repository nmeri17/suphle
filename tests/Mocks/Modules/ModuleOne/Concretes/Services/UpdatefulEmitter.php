<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

use Suphle\Events\EmitProxy;

use Suphle\Contracts\Events;

class UpdatefulEmitter extends SystemModelEditMock1
{
    use EmitProxy;

    public const UPDATE_ERROR = "update_error";

    public function __construct(private readonly Events $eventManager)
    {

        //
    }

    public function updateModels(object $baseModel): bool
    {
    	$this->emitHelper(self::UPDATE_ERROR, $baseModel); // one of the handlers here is expected to rollback updates before it and prevent ours below from running

        return $baseModel->update(["is_admin" => false]); // since event listener doesn't implement ServiceErrorCatcher, this method should terminate and return value of [failureState]
    }

    public function failureState(string $method)
    {

        return false;
    }
}
