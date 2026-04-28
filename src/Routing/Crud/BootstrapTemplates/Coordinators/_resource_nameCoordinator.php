<?php
namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\{Json, Markup, Reload};
use Suphle\Security\CSRF\CsrfGenerator;
use Suphle\Request\PayloadStorage;
use Suphle\Contracts\IO\Session;
use _modules_shell\_module_name\PayloadReaders\{Base_resource_nameBuilder, Search_resource_nameBuilder, _resource_nameSavePayload};
use _modules_shell\_module_name\Services\Eloquent\{_resource_nameAccessor, _resource_nameSearcher};

#[RoutePrefix(prefix: "_resource_name", _mirror_config)]
class _resource_nameCoordinator extends BaseCoordinator
{
    public function __construct(
        protected readonly CsrfGenerator $csrf,
        protected readonly Session $sessionClient,
        protected readonly _resource_nameAccessor $accessor,
        protected readonly _resource_nameSearcher $searcher
    ) {}

    #[Route("/")]
    public function index(): Markup
    {
        return new Markup('_resource_name.index', [
            'items' => $this->accessor->paginate()
        ]);
    }

    #[Route("/create")]
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
    public function store(_resource_nameSavePayload $payload): Reload
    {
        $this->accessor->createSingle($payload->getDomainObject());
        
        return new Reload();
    }

    #[Route("{id}", HttpMethod::PUT)]
    #[ValidationRules([
        "name" => "required|string|max:255",
        "description" => "nullable|string"
    ])]
    public function update(Base_resource_nameBuilder $builder, _resource_nameSavePayload $payload): Reload
    {
        // We use the builder for the WHERE clause and the payload for the DATA
        // MultiUserModelEdit interceptor will automatically validate _collision_protect
        
        $this->accessor->updateResource(
            $builder->getBuilder(), 
            $payload->getDomainObject()
        );
        
        return new Reload();
    }

    #[Route("{/id}")]
    public function show(Base_resource_nameBuilder $builder): Markup
    {
        return new Markup('_resource_name.show', [
            'item' => $this->accessor->getResource($builder->getBuilder())
        ]);
    }

    #[Route("{id}//edit")]
    public function edit(Base_resource_nameBuilder $builder): Markup
    {
        return new Markup('_resource_name.edit', [
            'item' => $this->accessor->getResource($builder->getBuilder()),
            CsrfGenerator::TOKEN_FIELD => $this->csrf->newToken()
        ]);
    }

    #[Route("{id}", HttpMethod::DELETE)]
    public function destroy(Base_resource_nameBuilder $builder): Json
    {
        $this->accessor->deleteResource($builder->getBuilder());
        return new Json(['message' => 'Deleted successfully']);
    }

    #[Route("/search")]
    #[ValidationRules(["query" => "required"])]
    public function search(Search_resource_nameBuilder $searchBuilder): Markup
    {
        $results = $this->searcher->convertToQuery(
            $searchBuilder->getBuilder(), ["query"]
        )->paginate();
        
        return new Markup('_resource_name.search', ['results' => $results]);
    }
}