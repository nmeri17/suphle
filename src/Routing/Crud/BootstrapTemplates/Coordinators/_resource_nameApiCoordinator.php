<?php

namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\{Json, Reload};
use _modules_shell\_module_name\PayloadReaders\{Base_resource_nameBuilder, Search_resource_nameBuilder};

#[RoutePrefix("api/_resource_name")]
class _resource_nameApiCoordinator extends ServiceCoordinator
{
    use _resource_nameGenericCoordinator;

    public function __construct(
        protected readonly _resource_nameSearcher $_resource_nameSearcher
    ) {
        //
    }

    #[Route("", HttpMethod::GET)]
    public function index(): Json
    {
        $items = $this->_resource_nameAccessor->getAll();
        
        return new Json(['data' => $items]);
    }

    #[Route("", HttpMethod::POST)]
    #[ValidationRules([
        "name" => "required|string|max:255",
        "description" => "nullable|string"
    ])]
    public function store(): Json
    {
        $data = $this->payloadReader->getAll();
        $item = $this->_resource_nameAccessor->create($data);
        
        return new Json([
            'message' => '_resource_name created successfully',
            'data' => $item
        ]);
    }

    #[Route("{id}", HttpMethod::GET)]
    public function show(int $id): Json
    {
        $item = $this->_resource_nameAccessor->find($id);
        
        return new Json(['data' => $item]);
    }

    #[Route("{id}", HttpMethod::PUT)]
    #[ValidationRules([
        "id" => "required|numeric|exists:_resource_name,id",
        "name" => "required|string|max:255",
        "description" => "nullable|string"
    ])]
    public function update(Base_resource_nameBuilder $_resource_nameBuilder): Json
    {
        $data = $this->payloadReader->getAll();
        $item = $this->_resource_nameAccessor->update($_resource_nameBuilder->getBuilder(), $data);
        
        return new Json([
            'message' => '_resource_name updated successfully',
            'data' => $item
        ]);
    }

    #[Route("{id}", HttpMethod::DELETE)]
    #[ValidationRules([
        "id" => "required|numeric|exists:_resource_name,id"
    ])]
    public function destroy(int $id): Json
    {
        $this->_resource_nameAccessor->delete($id);
        
        return new Json(['message' => '_resource_name deleted successfully']);
    }

    #[Route("search", HttpMethod::GET)]
    #[ValidationRules([
        "query" => "required|alphanumeric"
    ])]
    public function search(Search_resource_nameBuilder $searchBuilder): Json
    {
        $results = $this->_resource_nameSearcher->convertToQuery(
            $searchBuilder->getBuilder(), ["query"]
        )->paginate();
        
        return new Json(['data' => $results]);
    }
}
