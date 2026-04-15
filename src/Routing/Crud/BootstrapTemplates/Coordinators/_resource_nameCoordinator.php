<?php

namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\{Json, Markup, Reload};
use Suphle\Security\CSRF\CsrfGenerator;
use Suphle\Contracts\IO\Session;
use _modules_shell\_module_name\PayloadReaders\{Base_resource_nameBuilder, Search_resource_nameBuilder};
use _modules_shell\_module_name\Services\Eloquent\{_resource_nameAccessor, _resource_nameSearcher};

#[RoutePrefix(prefix: "_resource_name", _mirror_config)]
class _resource_nameCoordinator extends ServiceCoordinator
{

    public function __construct(
        protected readonly PayloadStorage $payloadStorage,
        protected readonly CsrfGenerator $csrf,
        protected readonly Session $sessionClient,
        protected readonly _resource_nameAccessor $_resource_nameAccessor,
        protected readonly Search_resource_nameBuilder $_resource_nameSearcher
    ) {
        //
    }

    #[Route("", HttpMethod::GET)]
    public function index(): Markup
    {
        return new Markup('_resource_name.index', [
            'items' => $this->_resource_nameAccessor->getAll()
        ]);
    }

    #[Route("create", HttpMethod::GET)]
    public function create(): Markup
    {
        return new Markup('_resource_name.create', [
            CsrfGenerator::TOKEN_FIELD => $this->csrf->newToken()
        ]);
    }

    #[Route("", HttpMethod::POST)]
    #[ValidationRules([
        "name" => "required|string|max:255",
        "description" => "nullable|string"
    ])]
    public function store(): Reload
    {
        $data = $this->payloadReader->getAll();
        $this->_resource_nameAccessor->create($data);
        
        return new Reload();
    }

    #[Route("{id}", HttpMethod::GET)]
    public function show(int $id): Markup
    {
        $item = $this->_resource_nameAccessor->find($id);
        
        return new Markup('_resource_name.show', [
            'item' => $item
        ]);
    }

    #[Route("{id}/edit", HttpMethod::GET)]
    #[ValidationRules([
        "id" => "required|numeric|exists:_resource_name,id"
    ])]
    public function edit(Base_resource_nameBuilder $_resource_nameBuilder): Markup
    {
        $data = $this->_resource_nameAccessor->getResource($_resource_nameBuilder->getBuilder());
        
        return new Markup('_resource_name.edit', [
            'item' => $data,
            CsrfGenerator::TOKEN_FIELD => $this->csrf->newToken()
        ]);
    }

    #[Route("{id}", HttpMethod::PUT)]
    #[ValidationRules([
        "id" => "required|numeric|exists:_resource_name,id",
        "name" => "required|string|max:255",
        "description" => "nullable|string"
    ])]
    public function update(Base_resource_nameBuilder $_resource_nameBuilder): Reload
    {
        $data = $this->payloadReader->getAll();
        $this->_resource_nameAccessor->update($_resource_nameBuilder->getBuilder(), $data);
        
        return new Reload();
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
    public function search(Search_resource_nameBuilder $searchBuilder): Markup
    {
        $results = $this->_resource_nameSearcher->convertToQuery(
            $searchBuilder->getBuilder(), ["query"]
        )->paginate();
        
        return new Markup('_resource_name.search', [
            'results' => $results
        ]);
    }

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
