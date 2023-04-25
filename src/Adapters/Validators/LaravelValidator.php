<?php

namespace Suphle\Adapters\Validators;

use Suphle\Contracts\Requests\RequestValidator;

use Illuminate\{Validation\Factory, Support\MessageBag};

class LaravelValidator implements RequestValidator
{
    protected MessageBag $errorHolder;

    public function __construct(protected readonly Factory $client)
    {

        //
    }

    public function validate(array $parameters, array $rules): void
    {

        $validator = $this->client->make($parameters, $rules);

        $this->errorHolder = $validator->errors();
    }

    public function getErrors(): iterable
    {

        return $this->errorHolder->messages();
    }
}
