<?php
namespace Suphle\Services\Structures;

use Suphle\Contracts\Services\CallInterceptors\SystemModelEdit;

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, DomainService};

#[InterceptsCalls(SystemModelEdit::class)]
#[VariableDependencies(["setPayloadStorage"])]
#[DomainService(mutation: true)]
abstract class BaseSystemModelEdit implements SystemModelEdit {

    use BaseErrorCatcherService;
}