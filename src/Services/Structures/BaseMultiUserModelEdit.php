<?php
namespace Suphle\Services\Structures;

use Suphle\Contracts\Services\CallInterceptors\MultiUserModelEdit;

use Suphle\Services\Decorators\{InterceptsCalls, VariableDependencies, DomainService};

#[InterceptsCalls(MultiUserModelEdit::class)]
#[VariableDependencies(["setPayloadStorage"])]
#[DomainService(mutation: true)]
abstract class BaseMultiUserModelEdit implements MultiUserModelEdit {
	
    use BaseErrorCatcherService;
}