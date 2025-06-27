<?php

namespace Suphle\Config;

use Suphle\Contracts\Config\{ExceptionInterceptor, ModuleFiles};

use Suphle\Exception\Explosives\{EditIntegrityException, NotFoundException, Unauthenticated, UnauthorizedServiceAccess, UnverifiedAccount, ValidationFailure, UnexpectedAuthentication};

use Suphle\Exception\Diffusers\{GenericDiffuser, NotFoundDiffuser, ValidationFailureDiffuser, UnauthorizedDiffuser, UnauthenticatedDiffuser, StaleEditDiffuser, UnverifiedAccountDiffuser, UnexpectedAuthenticationDiffuser};

class ExceptionConfig implements ExceptionInterceptor
{
    public function __construct(protected readonly ModuleFiles $fileConfig)
    {

        //
    }

    public function getHandlers(): array
    {

        return [

            EditIntegrityException::class => StaleEditDiffuser::class,

            NotFoundException::class => NotFoundDiffuser::class,

            Unauthenticated::class => UnauthenticatedDiffuser::class,

            UnauthorizedServiceAccess::class => UnauthorizedDiffuser::class,

            UnverifiedAccount::class => UnverifiedAccountDiffuser::class,

            ValidationFailure::class => ValidationFailureDiffuser::class,

            UnexpectedAuthentication::class => UnexpectedAuthenticationDiffuser::class
        ];
    }

    public function defaultHandler(): string
    {

        return GenericDiffuser::class;
    }

    public function shutdownLog(): string
    {

        return $this->fileConfig->activeModulePath() . "shutdown-log.txt";
    }

    /**
     * {@inheritdoc}
    */
    public function shutdownText(): string
    {

        return "Unable to handle this request :( But not to worry; our engineers are on top of the situation";
    }
}
