<?php
	namespace Suphle\Routing;

	use Suphle\Contracts\Routing\{ApiRouteCollection, CrudBuilder};

	use Suphle\Auth\Storage\TokenStorage;

	use Suphle\Routing\Crud\ApiBuilder;

	class BaseApiCollection extends BaseCollection implements ApiRouteCollection {

		protected string $collectionParent = BaseApiCollection::class;

		public function __construct (
			
			protected readonly CanaryValidator $canaryValidator,

			protected readonly MethodSorter $methodSorter,

			TokenStorage $authStorage
		) {

			$this->authStorage = $authStorage;
		}

		public function _crudJson ():CrudBuilder {

			$this->crudMode = true;

			return new ApiBuilder($this );
		}
	}
?>