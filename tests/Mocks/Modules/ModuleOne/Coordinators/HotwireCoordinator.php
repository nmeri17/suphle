<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\ServiceCoordinator;

use Suphle\Routing\Attributes\{Route, HttpMethod};

use Suphle\Response\Format\{Redirect, Markup};

use Suphle\Exception\Diffusers\ValidationFailureDiffuser;

use Suphle\Adapters\Presentation\Hotwire\Formats\{RedirectHotwireStream, ReloadHotwireStream};

use Suphle\Adapters\Orms\Eloquent\Models\ModelDetail;

use Suphle\Tests\Mocks\Models\Eloquent\Employment;

use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\{BaseEmploymentBuilder, EmploymentId2Builder};

use Suphle\Services\Decorators\ValidationRules;

class HotwireCoordinator extends ServiceCoordinator
{
    #[Route("init-post")]
    public function loadForm(): Markup
    {
        return new Markup("loadForm", "secure-some.edit-form");
    }

    #[Route("regular-markup", method: HttpMethod::POST)]
    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function regularFormResponse(BaseEmploymentBuilder $employmentBuilder): Redirect
    {
        // Example validation and redirect
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
        
        return $renderer->addReplace(
            "hotwireReplace",
            $this->getStreamActionTarget(),
            "hotwire/replace-fragment"
        )
        ->addBefore(
            "hotwireBefore",
            $this->getStreamActionTarget(),
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
        
        return $renderer->addAfter(
            "hotwireAfter",
            $this->getStreamActionTarget(),
            "hotwire/after-fragment"
        )
        ->addUpdate(
            "hotwireUpdate",
            $this->getStreamActionTarget(),
            "hotwire/update-fragment"
        );
    }

    #[Route("no-replace-node", method: HttpMethod::POST)]
    public function noReplaceNode(BaseEmploymentBuilder $employmentBuilder): RedirectHotwireStream
    {
        $renderer = (new RedirectHotwireStream(fn () => "/"))
            ->addAppend(
                "hotwireReplace",
                $this->getStreamActionTarget(),
                "hotwire/after-fragment"
            )
            ->addBefore(
                "hotwireBefore",
                $this->getStreamActionTarget(),
                "hotwire/before-fragment"
            );

        return $renderer;
    }

    #[Route("delete-single", method: HttpMethod::DELETE)]
    public function deleteSingle(EmploymentId2Builder $employmentBuilder): RedirectHotwireStream
    {
        $renderer = (new RedirectHotwireStream(fn () => "/"))
            ->addRemove(
                "hotwireDelete",
                $this->getStreamActionTarget()
            );

        return $renderer;
    }

    #[Route("combine-delete", method: HttpMethod::DELETE)]
    public function combineDelete(EmploymentId2Builder $employmentBuilder): RedirectHotwireStream
    {
        $renderer = new RedirectHotwireStream(fn () => "/");
        
        return $renderer->addRemove(
            "hotwireDelete",
            $this->getStreamActionTarget()
        )
        ->addAfter(
            "hotwireAfter",
            $this->getStreamActionTarget(),
            "hotwire/after-fragment"
        );
    }

    /**
     * On success, creates a turbo stream for given element. On failure, should attempt to replace/update the form from which request originated
    */
    public function getStreamActionTarget(string $formTarget = "update-form"): callable
    {
        return function () use ($formTarget) {
            $responseBody = $this->rawResponse;

            if (!array_key_exists(ValidationFailureDiffuser::ERRORS_PRESENCE, $responseBody)) {
                return (new ModelDetail())->idFromModel($responseBody["data"]);
            }

            return $formTarget;
        };
    }

    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function hotwireFormResponse(BaseEmploymentBuilder $employmentBuilder): array
    {
        $employment = $this->employmentService->create($employmentBuilder);

        return [
            "data" => $employment,
            "message" => "Employment created successfully"
        ];
    }

    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function hotwireReplace(BaseEmploymentBuilder $employmentBuilder): array
    {
        $employment = $this->employmentService->create($employmentBuilder);

        return [
            "data" => $employment
        ];
    }

    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function hotwireBefore(EmploymentId2Builder $employmentBuilder): array
    {
        $employment = $this->employmentService->create($employmentBuilder);

        return [
            "data" => $employment
        ];
    }

    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function hotwireAfter(BaseEmploymentBuilder $employmentBuilder): array
    {
        $employment = $this->employmentService->create($employmentBuilder);

        return [
            "data" => $employment
        ];
    }

    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function hotwireUpdate(EmploymentId2Builder $employmentBuilder): array
    {
        $employment = $this->employmentService->update($employmentBuilder);

        return [
            "data" => $employment
        ];
    }

    #[ValidationRules([
        'field1' => 'required|string',
        'field2' => 'required|integer|min:1'
    ])]
    public function hotwireDelete(BaseEmploymentBuilder $employmentBuilder): array
    {
        $employment = $this->employmentService->delete($employmentBuilder);

        return [
            "data" => $employment
        ];
    }
}
