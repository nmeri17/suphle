<?php

namespace Suphle\Tests\Integration\Services\Proxies\SystemModelEdit;

use Suphle\Hydration\Container;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Suphle\Tests\Mocks\Models\Eloquent\User as EloquentUser;

class UpdateEventErrorTest extends ModuleLevelTest
{
	use BaseDatabasePopulator;

    protected function getActiveEntity(): string
    {

        return EloquentUser::class;
    }
    
    protected function getModules(): array
    {

        return [new ModuleOneDescriptor(new Container())];
    }

    public function test_error_in_event_handler_terminates_transaction()
    {

    	$adminStatus = true; // given

        $model = $this->replicator

        ->modifyInsertion(1, ["is_admin" => $adminStatus])->first();

        $this->getModuleFor(ModuleOne::class)

        ->systemUpdateErrorEvent($model); // when

        $onlineValue = $this->replicator

        ->getSpecificEntities(1, ["id" => $model->id])->first();

        $this->assertEquals($onlineValue->is_admin, $adminStatus); // then
    }
}
