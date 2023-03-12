<?php
	namespace Suphle\Routing;

	use Suphle\Contracts\Routing\{ApiRouteCollection, CrudBuilder};

	use Suphle\Routing\Crud\ApiBuilder;

	class BaseApiCollection extends BaseCollection implements ApiRouteCollection {

		protected string $collectionParent = BaseApiCollection::class;

		public function __construct(CanaryValidator $validator, MethodSorter $methodSorter) {

			//
		}

		public function _crudJson ():CrudBuilder {

			$this->crudMode = true;

			return new ApiBuilder($this );
		}
	}
?>