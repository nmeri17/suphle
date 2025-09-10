<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, CollectionFlow, SingleFlow, CollectionFlowOperation, SingleFlowOperation};
use Suphle\Response\Format\Json;
use Suphle\Services\Structures\ModellessPayload;

use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\ReadsId;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{DummyModels, BlankUpdateless};

class FlowCoordinator extends ServiceCoordinator
{
    public function __construct(protected readonly DummyModels $dummyModels, protected readonly BlankUpdateless $blankService)
    {
        //
    }

    #[Route("no-flow")]
    public function noFlow(): Json
    {
        return new Json([]);
    }

    #[Route("posts/{id}")]
    public function posts(): Json
    {
        return new Json([]);
    }

    #[Route("preloaded")]
    public function preloaded(): Json
    {
        return new Json([]);
    }

    #[Route("flow-with-flow/{id}")]
    public function flowWithFlow(): Json
    {
        return new Json([]);
    }

    #[Route("internal-flow/{id}")]
    public function internalFlow(): Json
    {
        return new Json([]);
    }

    #[Route("combine-flows")]
    public function combineFlows(): Json
    {
        return new Json([]);
    }

    #[Route("single-node")]
    public function singleNode(): Json
    {
        return new Json([]);
    }

    #[Route("from-service")]
    public function fromService(): Json
    {
        return new Json([]);
    }

    #[Route("pipe-to")]
    public function pipeTo(): Json
    {
        return new Json([]);
    }

    #[Route("one-of")]
    public function oneOf(): Json
    {
        return new Json([]);
    }

    #[Route("user-content/{id}")]
    public function userContent(): Json
    {
        return new Json([]);
    }

    #[Route("flow-to-module3")]
    public function flowToModule3(): Json
    {
        return new Json([]);
    }

    // Collection node - Iterative operation
    #[Route('catalog/{id}')]
    #[CollectionFlow(
        target: 'books/{id}',
        source: 'data',
        operation: CollectionFlowOperation::PIPE_TO
    )]
    public function getCatalog(): Json
    {
        return new Json([
            'data' => [
                ['id' => 1, 'name' => 'Book 1'],
                ['id' => 2, 'name' => 'Book 2'],
                ['id' => 3, 'name' => 'Book 3']
            ]
        ]);
    }

    // Collection node - Concatenated indexes
    #[Route('catalog/special')]
    #[CollectionFlow(
        target: 'special-books',
        source: 'data',
        operation: CollectionFlowOperation::AS_ONE,
        columnName: 'id'
    )]
    public function getSpecialCatalog(): Json
    {
        return new Json([
            'data' => [
                ['id' => 1, 'name' => 'Special Book 1'],
                ['id' => 2, 'name' => 'Special Book 2']
            ]
        ]);
    }

    // Collection node - Contrasting indexes
    #[Route('catalog/range')]
    #[CollectionFlow(
        target: 'isbn/between',
        source: 'data',
        operation: CollectionFlowOperation::IN_RANGE,
        rangeContext: ['min', 'max']
    )]
    public function getCatalogRange(): Json
    {
        return new Json([
            'data' => [
                ['id' => 1, 'isbn' => 100],
                ['id' => 2, 'isbn' => 200],
                ['id' => 3, 'isbn' => 300]
            ]
        ]);
    }

    // Single node - Query updating
    #[Route('products/{id}')]
    #[SingleFlow(
        target: '/products/recommended',
        source: 'next_page_url',
        operation: SingleFlowOperation::ALTERS_QUERY
    )]
    public function getProduct(): Json
    {
        return new Json([
            'next_page_url' => '/products/recommended?page=2&category=electronics'
        ]);
    }

    // Collection node - Custom service
    #[Route('catalog/service')]
    #[CollectionFlow(
        target: 'segment',
        source: 'data',
        operation: CollectionFlowOperation::SET_FROM_SERVICE,
        serviceClass: \Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\FlowService::class,
        serviceMethod: 'customHandlePrevious'
    )]
    public function getCatalogWithService(): Json
    {
        return new Json([
            'data' => [
                ['id' => 1, 'name' => 'Service Book 1'],
                ['id' => 2, 'name' => 'Service Book 2']
            ]
        ]);
    }
}
