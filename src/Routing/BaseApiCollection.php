<?php

namespace Suphle\Routing;

use Suphle\Contracts\Routing\{ApiRouteCollection, Crud\CrudBuilder};

use Suphle\Auth\Storage\TokenStorage;

use Suphle\Routing\Crud\ApiBuilder;

class BaseApiCollection extends BaseCollection implements ApiRouteCollection
{
    protected string $collectionParent = BaseApiCollection::class,

    $authStorageName = TokenStorage::class;

    public function _crudJson(): CrudBuilder
    {

        $this->crudMode = true;

        return new ApiBuilder($this);
    }
}
