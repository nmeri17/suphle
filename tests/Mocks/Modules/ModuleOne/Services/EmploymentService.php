<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Services;

use Suphle\Services\Structures\BaseErrorCatcherService;
use Suphle\Services\Decorators\{DomainService, InterceptsCalls};
use Suphle\Contracts\Services\CallInterceptors\SystemModelEdit;
use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\{BaseEmploymentBuilder, EmploymentId2Builder};

#[DomainService(mutation: true)]
#[InterceptsCalls(SystemModelEdit::class)]
class EmploymentService implements SystemModelEdit
{
    use BaseErrorCatcherService;

    /**
     * The single, required strict Mutative Action contract method.
     * Suphle automatically wraps this method in a managed database transaction.
     */
    public function updateModels(object $payload): array
    {
        return [
            "data" => [
                "id" => method_exists($payload, 'getId') ? $payload->getId() : 100,
                "status" => "persisted"
            ]
        ];
    }

    /**
     * Required by SystemModelEdit to apply SELECT ... FOR UPDATE locks 
     * on active rows before updateModels executes.
     */
    public function modelsToUpdate(object $payload): iterable
    {
        return [];
    }

    /**
     * A safe Read/Fetch operation to build secondary Hotwire fragment segments.
     * Wrapped by the class interceptor proxy to catch unforeseen query failures.
     */
    public function fetchAlternateFragmentData(object $payload): array
    {
        return [
            "data" => [
                "id" => 101,
                "status" => "fetched_variant"
            ]
        ];
    }

    /**
     * Safe Read/Fetch operation for auxiliary layouts.
     */
    public function fetchAncillaryRecord(EmploymentId2Builder $builder): array
    {
        return [
            "data" => [
                "id" => $builder->id ?? 200,
                "type" => "ancillary_view"
            ]
        ];
    }
}