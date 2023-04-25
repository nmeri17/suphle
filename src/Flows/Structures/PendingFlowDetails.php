<?php

namespace Suphle\Flows\Structures;

use Suphle\Contracts\{Auth\AuthStorage, Presentation\BaseRenderer};

use Suphle\Flows\OuterFlowWrapper;

/**
 * This is what is received from the currently handled request. It is stored and during handling later, specifics of the flow are extracted and handled
*/
class PendingFlowDetails
{
    private $userId;

    public function __construct(protected readonly BaseRenderer $renderer, protected readonly AuthStorage $authStorage)
    {

        $this->getUserId(); // trigger property storage before task serialization
    }

    public function getStoredUserId(): string
    {

        return $this->userId;
    }

    public function getRenderer(): BaseRenderer
    {

        return $this->renderer;
    }

    /**
    * Whether a sub-flow or transition from organic flow, all flow queueing is triggered by a user request. This argument is that user
    */
    protected function getUserId(): string
    {

        if (is_null($this->userId)) {

            $user = $this->authStorage->getUser();

            $this->userId = !is_null($user) ? strval($user->getId()) :

            OuterFlowWrapper::ALL_USERS;
        }

        return $this->userId;
    }

    public function getAuthStorage(): string
    {

        return $this->authStorage::class;
    }
}
