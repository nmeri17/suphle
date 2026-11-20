<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Services;

use Suphle\Services\Structures\BaseSystemModelEdit;

class SystemModelEditMock1 extends BaseSystemModelEdit {

    public function updateModels(object $baseModel):bool
    {

        return true;
    }

    public function modelsToUpdate (object $baseModel):iterable
    {

        return [$baseModel];
    }

    public function unrelatedToUpdate():void
    {
    }
}
