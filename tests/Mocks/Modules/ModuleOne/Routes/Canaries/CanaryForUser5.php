<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries;

use Suphle\Contracts\{Routing\CanaryGateway, Auth\AuthStorage};

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections\CollectionForUser5;

class CanaryForUser5 implements CanaryGateway
{
    public function __construct(protected readonly AuthStorage $authStorage)
    {

        //
    }

    public function willLoad(): bool
    {

        return $this->authStorage->getId() == 5;
    }

    public function entryClass(): string
    {

        return CollectionForUser5::class;
    }
}
