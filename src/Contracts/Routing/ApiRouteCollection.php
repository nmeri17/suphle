<?php

namespace Suphle\Contracts\Routing;

use Suphle\Contracts\Routing\Crud\CrudBuilder;

interface ApiRouteCollection
{
    public function _crudJson(): CrudBuilder;
}
