<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Services;

use Suphle\Services\Structures\BaseSystemModelEdit;

use Suphle\Tests\Mocks\Modules\ModuleOne\PayloadReaders\EmploymentId2Builder;

class EmploymentService extends BaseSystemModelEdit {
    
    public function updateModels(object $builder): array
    {
        return $builder->update($this->payloadStorage->fullPayload());
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