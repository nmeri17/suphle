<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\V1;

use Suphle\Tests\Mocks\Modules\ModuleOne\Interfaces\RewriteSpace;

class RewriteSpaceImpl implements RewriteSpace
{
    public function getValue(): int
    {

        return 10;
    }
}
