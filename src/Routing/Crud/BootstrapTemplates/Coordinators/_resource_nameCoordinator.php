<?php

namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

use Suphle\Request\PayloadStorage;

use Suphle\Security\CSRF\CsrfGenerator;

use Suphle\Contracts\IO\Session;

use _modules_shell\_module_name\PayloadReaders\{Base_resource_nameBuilder, Search_resource_nameBuilder};

use _modules_shell\_module_name\Services\Eloquent\{_resource_nameAccessor, _resource_nameSearcher};

class _resource_nameCoordinator extends ServiceCoordinator
{
    use _resource_nameGenericCoordinator;

    public function __construct(
        protected readonly PayloadStorage $payloadStorage,
        protected readonly CsrfGenerator $csrf,
        protected readonly Session $sessionClient,
        protected readonly _resource_nameAccessor $_resource_nameAccessor,
        protected readonly Search_resource_nameBuilder $_resource_nameSearcher
    ) {

        //
    }

    public function showCreateForm(): iterable
    {

        return $this->copyValidationErrors([

            CsrfGenerator::TOKEN_FIELD => $this->csrf->newToken()
        ]);
    }

    #[ValidationRules([
        "query" => "required|alphanumeric"
    ])]
    public function showSearchForm(Search_resource_nameBuilder $searchBuilder): iterable
    {

        return [
        	"results" => $this->_resource_nameSearcher->convertToQuery(

				$searchBuilder->getBuilder(), ["query"]
			)->paginate()
        ];
    }

    #[ValidationRules([
        "id" => "required|numeric|exists:_resource_name,id"
    ])]
    public function showEditForm(Base_resource_nameBuilder $_resource_nameBuilder): iterable
    {

        return [
        	"data" => $this->_resource_nameAccessor

        	->getResource($_resource_nameBuilder->getBuilder())
        ];
    }
}
