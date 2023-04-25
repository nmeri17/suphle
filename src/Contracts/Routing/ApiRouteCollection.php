<?php

namespace Suphle\Contracts\Routing;

interface ApiRouteCollection
{
    public function _crudJson(): CrudBuilder;
}
