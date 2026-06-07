<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\{Redirect, Markup};
use Suphle\Adapters\Presentation\Hotwire\Formats\{RedirectHotwireStream, ReloadHotwireStream};
use Suphle\Adapters\Orms\Eloquent\Models\ModelDetail;
use Suphle\Tests\Mocks\Modules\ModuleOne\{PayloadReaders\BaseEmploymentBuilder, PayloadReaders\EmploymentId2Builder, Services\EmploymentService};

#[RoutePrefix("/hotwire")]
class HotwireCoordinator extends BaseCoordinator
{
    public function __construct(
        protected readonly EmploymentService $employmentService
    ) {}

    #[Route("init-post", method: HttpMethod::GET)]
    public function loadForm(): Markup
    {
        return new Markup("secure-some.edit-form", []);
    }

    #[Route("regular-markup", method: HttpMethod::POST)]
    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function regularFormResponse(BaseEmploymentBuilder $employmentBuilder): Redirect
    {
        // One distinct mutative action executed through the proxy contract
        $this->employmentService->updateModels($employmentBuilder);

        return new Redirect(fn () => "/");
    }

    #[Route("hotwire-redirect", method: HttpMethod::POST)]
    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function hotwireRedirect(BaseEmploymentBuilder $employmentBuilder): RedirectHotwireStream
    {
        $renderer = new RedirectHotwireStream(fn () => "/");
        
        // 1. One true mutative write action inside a managed transaction block
        $mutationResult = $this->employmentService->updateModels($employmentBuilder);

        // 2. Chained layouts are safely populated via read fetches
        return $renderer->addReplace(
            $mutationResult,
            $this->extractId(...),
            "hotwire/replace-fragment"
        )
        ->addBefore(
            $this->employmentService->fetchAlternateFragmentData($employmentBuilder),
            $this->extractId(...),
            "hotwire/before-fragment"
        );
    }

    #[Route("hotwire-reload", method: HttpMethod::PUT)]
    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function hotwireReload(BaseEmploymentBuilder $employmentBuilder): ReloadHotwireStream
    {
        $renderer = new ReloadHotwireStream();
        
        // Mutative write action
        $mutationResult = $this->employmentService->updateModels($employmentBuilder);

        // Chained update renders fetched variants safely wrapped against failure
        return $renderer->addAfter(
            $mutationResult,
            $this->extractId(...),
            "hotwire/after-fragment"
        )
        ->addUpdate(
            $this->employmentService->fetchAlternateFragmentData($employmentBuilder),
            $this->extractId(...),
            "hotwire/update-fragment"
        );
    }

    #[Route("no-replace-node", method: HttpMethod::POST)]
    #[ValidationRules([
        'field1' => 'required|string'
    ])]
    public function noReplaceNode(BaseEmploymentBuilder $employmentBuilder): RedirectHotwireStream
    {
        $mutationResult = $this->employmentService->updateModels($employmentBuilder);

        return (new RedirectHotwireStream(fn () => "/"))
            ->addAppend(
                $mutationResult,
                $this->extractId(...),
                "hotwire/after-fragment"
            )
            ->addBefore(
                $this->employmentService->fetchAlternateFragmentData($employmentBuilder),
                $this->extractId(...),
                "hotwire/before-fragment"
            );
    }

    #[Route("delete-single", method: HttpMethod::DELETE)]
    #[ValidationRules([
        'id' => 'required|integer'
    ])]
    public function deleteSingle(EmploymentId2Builder $employmentBuilder): RedirectHotwireStream
    {
        // Mutative write triggers deletion processing via contract entry assigned to payload stream visibility
        $mutationResult = $this->employmentService->updateModels($employmentBuilder);

        return (new RedirectHotwireStream(fn () => "/items"))
            ->addRemove(
                $mutationResult,
                fn () => "#employment_" . $employmentBuilder->id
            );
    }

    #[Route("combine-delete", method: HttpMethod::DELETE)]
    #[ValidationRules([
        'id' => 'required|integer'
    ])]
    public function combineDelete(EmploymentId2Builder $employmentBuilder): RedirectHotwireStream
    {
        $renderer = new RedirectHotwireStream(fn () => "/");
        
        // Mutative structural deletion action
        $mutationResult = $this->employmentService->updateModels($employmentBuilder);
        
        // Safe data fetch wrapper for the secondary layout append tracking
        $ancillaryViewData = $this->employmentService->fetchAncillaryRecord($employmentBuilder);
        
        return $renderer->addRemove(
            $mutationResult,
            fn () => "#employment_" . $employmentBuilder->id
        )
        ->addAfter(
            $ancillaryViewData,
            $this->extractId(...),
            "hotwire/after-fragment"
        );
    }

    /**
     * Unified resolver for model DOM identifiers on success paths.
     */
    protected function extractId(array $result): string
    {
        return (new ModelDetail())->idFromModel($result["data"] ?? []);
    }
}