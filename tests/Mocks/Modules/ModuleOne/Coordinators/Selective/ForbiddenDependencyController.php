<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Selective;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};

use Suphle\Hydration\Container;

#[RoutePrefix("/forbid")]
class ForbiddenDependencyController extends BaseCoordinator
{
    public function __construct(protected readonly Container $container)
    {

        //
    }
}
