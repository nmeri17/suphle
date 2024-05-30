<?php

namespace Suphle\Exception\Explosives;

use Suphle\Contracts\Auth\AuthStorage;

use Exception;

class UnexpectedAuthentication extends Exception
{
    public function __construct() // not passing storage since it's irrelevant to user
    {

        //
    }
}
