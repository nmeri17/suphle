<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Controllers;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Routing\HttpMethod;
use Suphle\Response\Format\{Json, Markup};
use Suphle\Request\PayloadStorage;
use Suphle\Routing\Crud\BrowserBuilder;
use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\SystemModelEditMock1;
use Suphle\Tests\Mocks\Models\Eloquent\Employment;

#[RoutePrefix("crud")]
class CrudCoordinator extends ServiceCoordinator
{
    public function __construct(
        protected readonly SystemModelEditMock1 $editService,
        protected readonly PayloadStorage $payloadStorage
    ) {
        //
    }

    #[Route("envelope/create", HttpMethod::GET)]
    public function showCreateForm(): Markup
    {
        return new Markup("envelope.create", []);
    }

    #[Route("envelope", HttpMethod::POST)]
    #[ValidationRules(["title" => "required"])]
    public function saveNew(): Json
    {
        $blankModel = new Employment();

        $newModel = $blankModel->create(
            $this->payloadStorage->only([
                "title", "employer_id", "salary"
            ])
        );

        return new Json([
            BrowserBuilder::SAVE_NEW_KEY => $newModel
        ]);
    }

    #[Route("envelope", HttpMethod::GET)]
    public function showAll(): Markup
    {
        return new Markup("envelope.index", []);
    }

    #[Route("envelope/{id}", HttpMethod::GET)]
    public function showOne(): Markup
    {
        return new Markup("envelope.show", []);
    }

    #[Route("envelope/{id}", HttpMethod::PUT)]
    #[ValidationRules(["id" => "required"])]
    public function updateOne(): Json
    {
        return new Json([]);
    }

    #[Route("envelope/{id}", HttpMethod::DELETE)]
    #[ValidationRules(["id" => "required"])]
    public function deleteOne(): Json
    {
        return new Json([]);
    }

    #[Route("envelope/search", HttpMethod::GET)]
    public function showSearchForm(): Markup
    {
        return new Markup("envelope.search", []);
    }

    #[Route("usurp/{id}", HttpMethod::GET)]
    public function myOverride(): Markup
    {
        return new Markup("usurp.show-one", []);
    }

    #[Route("envelope/{id}/edit", HttpMethod::GET)]
    public function showEditForm(): Markup
    {
        return new Markup("envelope.edit", []);
    }

    // Disabled handlers for handicap routes
    #[Route("handicap", HttpMethod::GET)]
    public function showAllHandicap(): Markup
    {
        return new Markup("handicap.index", []);
    }

    #[Route("handicap/{id}", HttpMethod::GET)]
    public function showOneHandicap(): Markup
    {
        return new Markup("handicap.show", []);
    }

    #[Route("handicap/{id}", HttpMethod::PUT)]
    public function updateOneHandicap(): Json
    {
        return new Json([]);
    }

    #[Route("handicap/{id}", HttpMethod::DELETE)]
    public function deleteOneHandicap(): Json
    {
        return new Json([]);
    }

    #[Route("handicap/search", HttpMethod::GET)]
    public function showSearchFormHandicap(): Markup
    {
        return new Markup("handicap.search", []);
    }

    #[Route("handicap/{id}/edit", HttpMethod::GET)]
    public function showEditFormHandicap(): Markup
    {
        return new Markup("handicap.edit", []);
    }
} 