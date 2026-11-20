<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Paths;

use Suphle\Contracts\Auth\AuthStorage;

use Suphle\Request\{RouteRule, RequestDetails};

use Suphle\Routing\Structures\RouteInfo;

use Suphle\Tests\Mocks\Models\Eloquent\Employment;

class EmploymentEditRule extends RouteRule
{
    public function __construct(
        AuthStorage $authStorage,
        protected readonly Employment $model,
        protected readonly RequestDetails $requestDetails
    ) {

        parent::__construct($authStorage);
    }

    public function permit(): bool
    {

        return /*$user->isAdmin() &&*/ $this->authStorage->getId() == $this->getCreatorId(); // not necessary cuz of the preceding rule combined with this, unless we want to replace "&&" with "||"
    }

    protected function getCreatorId(): int
    {

        $employment = $this->model->find(
            $this->requestDetails->getComputedDetails()->getSegmentValue("id")
        );

        return $employment->employer->user_id;
    }
}
