<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

use Suphle\Services\UpdatelessService;

use Suphle\Contracts\Auth\AuthStorage;

// these methods are redundant to the class btw
class BlankUpdateless extends UpdatelessService
{
    public function __construct(protected readonly AuthStorage $authStorage)
    {

    }

    public function getUserId(): ?string
    {

        return $this->authStorage->getId();
    }

    public function modelsToUpdate (object $baseModel): array
    {

        return [];
    }
}
