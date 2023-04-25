<?php

namespace Suphle\Auth\Repositories;

use Suphle\Contracts\Auth\ColumnPayloadComparer;

use Suphle\Auth\Storage\SessionStorage;

use Suphle\Services\Decorators\ValidationRules;

class BrowserAuthRepo extends BaseAuthRepo
{
    public function __construct(
        protected readonly ColumnPayloadComparer $comparer,
        protected readonly SessionStorage $authStorage
    ) {

        //
    }

    #[ValidationRules([
        "email" => "required|email",

        "password" => "required|alpha_num|min:5"
    ])]
    public function successLogin(): iterable
    {

        return [$this->startSessionForCompared()];
    }
}
