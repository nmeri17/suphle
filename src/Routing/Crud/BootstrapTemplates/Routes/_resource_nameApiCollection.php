<?php

namespace _modules_shell\_module_name\Routes;

use Suphle\Routing\{BaseApiCollection, Decorators\HandlingCoordinator};

use _modules_shell\_module_name\Coordinators\_resource_nameApiCoordinator;

#[HandlingCoordinator(_resource_nameApiCoordinator::class)]
class _resource_nameApiCollection extends BaseApiCollection
{
    public function _resource_route()
    {

        $this->_crudJson()->registerCruds();
    }
}
