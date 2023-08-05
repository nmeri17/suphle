<?php

namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\Decorators\ValidationRules;

use Suphle\Routing\Crud\BrowserBuilder;

use _database_namespace\_resource_name;

use _modules_shell\_module_name\PayloadReaders\Base_resource_nameBuilder;

trait _resource_nameGenericCoordinator
{
    #[ValidationRules(["name" => "required"])]
    public function saveNew(): iterable
    {

        return [

            BrowserBuilder::SAVE_NEW_KEY => $this->_resource_nameAccessor

            ->createSingle($this->payloadStorage->fullPayload())
        ];
    }

    public function showAll(): iterable
    {

        return [

        	"data" => $this->_resource_nameAccessor->paginate()
        ];
    }

    #[ValidationRules([
        "id" => "required|numeric|exists:_resource_name,id"
    ])]
    public function showOne(Base_resource_nameBuilder $_resource_nameBuilder): iterable
    {

        return [

        	"data" => $this->_resource_nameAccessor

        	->getResource($_resource_nameBuilder->getBuilder())
        ];
    }

    #[ValidationRules([
        "id" => "required|numeric|exists:_resource_name,id"
    ])]
    public function updateOne(Base_resource_nameBuilder $_resource_nameBuilder): iterable
    {

        return [
        	"message" => $this->_resource_nameAccessor->updateResource(

        		$_resource_nameBuilder->getBuilder(),

        		$this->payloadStorage->fullPayload()
        	)
        ];
    }

    #[ValidationRules([
        "id" => "required|numeric|exists:_resource_name,id"
    ])]
    public function deleteOne(): iterable
    {

        return [
        	"message" => $this->_resource_nameAccessor->deleteById(

        		$this->payloadStorage->getKey("id")
        	)
        ];
    }
}
