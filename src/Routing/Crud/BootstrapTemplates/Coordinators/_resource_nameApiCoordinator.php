<?php

namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\ServiceCoordinator;

use Suphle\Request\PayloadStorage;

use _modules_shell\_module_name\PayloadReaders\Base_resource_nameBuilder;

class _resource_nameApiCoordinator extends ServiceCoordinator
{
    use _resource_nameGenericCoordinator;

    public function __construct(protected readonly PayloadStorage $payloadStorage)
    {

        //
    }

    public function getSearchResults(): iterable
    {

        return [];
    }
}
