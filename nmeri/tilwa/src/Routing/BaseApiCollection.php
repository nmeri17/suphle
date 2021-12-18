<?php
	namespace Tilwa\Routing;

	use Tilwa\Auth\TokenStorage;

	use Tilwa\Routing\Crud\{BaseBuilder, ApiBuilder};

	class BaseApiCollection extends BaseCollection implements ApiRouteCollection {

		protected $collectionParent = BaseApiCollection::class;

		public function __construct(CanaryValidator $validator, TokenStorage $authStorage, MiddlewareRegistry $middlewareRegistry) {

			parent::__construct($validator, $authStorage, $middlewareRegistry);
		}

		protected function _crudJson ():BaseBuilder {

			$this->crudMode = true;

			return new ApiBuilder($this );
		}
	}
?>