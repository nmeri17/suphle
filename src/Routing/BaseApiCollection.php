<?php
	namespace Suphle\Routing;

	use Suphle\Contracts\Routing\{ApiRouteCollection, CrudBuilder};

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Routing\Crud\ApiBuilder;

	class BaseApiCollection extends BaseCollection implements ApiRouteCollection {

		protected $collectionParent = BaseApiCollection::class;

		public function __construct(CanaryValidator $validator, TokenStorage $authStorage, MethodSorter $methodSorter) {

			parent::__construct($validator, $authStorage, $methodSorter);
		}

		public function _crudJson ():CrudBuilder {

			$this->crudMode = true;

			return new ApiBuilder($this );
		}
	}
?>