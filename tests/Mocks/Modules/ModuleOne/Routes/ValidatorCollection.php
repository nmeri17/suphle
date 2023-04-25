<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

use Suphle\Routing\BaseCollection;

use Suphle\Response\Format\{Json, Markup, Redirect};

use Suphle\Routing\Decorators\HandlingCoordinator;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ValidatorCoordinator;

#[HandlingCoordinator(ValidatorCoordinator::class)]
class ValidatorCollection extends BaseCollection
{
    public function POST__WITH__JSONh()
    {

        $this->_httpPost(new Json("postWithValidator"));
    }

    public function POST__WITH__HTMLh()
    {

        $this->_httpPost(new Redirect("postWithValidator", fn () => "/"));
    }

    public function POST__WITHOUTh()
    {

        $this->_httpPost(new Json("postNoValidator"));
    }

    public function GET__WITHOUTh()
    {

        $this->_httpGet(new Markup("handleGet", "secure-some.edit-form"));
    }
}
