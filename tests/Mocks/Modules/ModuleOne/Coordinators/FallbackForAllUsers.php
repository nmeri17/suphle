<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Response\Format\Json;

class FallbackForAllUsers
{
    public function secureRoute(): Json
    {
        return new Json(['secure' => false, 'fallback' => true, 'message' => 'Using stable version']);
    }
} 