<?php
	namespace Tilwa\Routing;

	use Tilwa\Auth\TokenStorage;

	use Tilwa\Routing\Crud\{BaseBuilder, ApiBuilder};

	use Tilwa\Request\PathAuthorizer;

	class BaseApiCollection extends BaseCollection implements ApiRouteCollection {

		protected $collectionParent = BaseApiCollection::class;

		public function __construct(CanaryValidator $validator, TokenStorage $authStorage, MiddlewareRegistry $middlewareRegistry, PathAuthorizer $pathAuthorizer) {

			parent::__construct($validator, $authStorage, $middlewareRegistry, $pathAuthorizer);
		}

		protected function _crudJson ():BaseBuilder {

			$this->crudMode = true;

			return new ApiBuilder($this );
		}
	}
?>