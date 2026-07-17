<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\Selective;

use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Services\BaseCoordinator;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\BlankUpdateless;

#[RoutePrefix("/blank")]
class BlankUpdatelessController extends BaseCoordinator
{
    public function __construct(protected readonly BlankUpdateless $dependency)
    {

        //
    }
}
