<?php
	namespace Tilwa\Routing;

	use Tilwa\Auth\TokenStorage;

	use Tilwa\Routing\Crud\ApiBuilder;

	class BaseApiCollection extends BaseCollection implements ApiRouteCollection {

		protected $collectionParent = BaseApiCollection::class;

		public function __construct(CanaryValidator $validator, TokenStorage $authStorage, MethodSorter $methodSorter) {

			parent::__construct($validator, $authStorage, $middlewareRegistry, $pathAuthorizer);
		}

		protected function _crudJson ():CrudBuilder {

			$this->crudMode = true;

			return new ApiBuilder($this );
		}
	}
?>