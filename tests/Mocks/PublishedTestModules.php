<?php

namespace Suphle\Tests\Mocks;

use Suphle\Modules\ModuleHandlerIdentifier;

use Suphle\Tests\Integration\Generic\TestsModuleList;

class PublishedTestModules extends ModuleHandlerIdentifier
{
    use TestsModuleList;

    public function __construct()
    {

        $this->setAllDescriptors();

        parent::__construct();
    }

    public function getModules(): array
    {

        return $this->getAllDescriptors();
    }
}
