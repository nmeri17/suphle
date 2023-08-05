<?php

namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

use Suphle\Request\PayloadStorage;

use _modules_shell\_module_name\PayloadReaders\{Base_resource_nameBuilder, Search_resource_nameBuilder};

class _resource_nameApiCoordinator extends ServiceCoordinator
{
    use _resource_nameGenericCoordinator;

    public function __construct(
    	protected readonly PayloadStorage $payloadStorage,
    	protected readonly _resource_nameSearcher $_resource_nameSearcher
    )
    {

        //
    }

    #[ValidationRules([
        "query" => "required|alphanumeric"
    ])]
    public function getSearchResults(Search_resource_nameBuilder $searchBuilder): iterable
    {

        return [
        	"results" => $this->_resource_nameSearcher->convertToQuery(

				$searchBuilder->getBuilder(), ["query"]
			)->paginate();
        ];
    }
}
