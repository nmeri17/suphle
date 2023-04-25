<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\ImageUploadCoordinator;

#[HandlingCoordinator(ImageUploadCoordinator::class)]
class ImageUploadCollection extends BaseCollection
{
    public function APPLY__ALLh()
    {

        $this->_httpPost(new Json("applyAllOptimizations"));
    }

    public function APPLY__NONEh()
    {

        $this->_httpPost(new Json("applyNoOptimization"));
    }

    public function APPLY__CROPh()
    {

        $this->_httpPost(new Json("applyThumbnail"));
    }
}
