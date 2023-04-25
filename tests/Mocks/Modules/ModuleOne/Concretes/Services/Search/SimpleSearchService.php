<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\Search;

use Suphle\Services\Search\SimpleSearch;

class SimpleSearchService extends SimpleSearch
{
    public function custom_filter($model, $value)
    {

        return $model;
    }
}
