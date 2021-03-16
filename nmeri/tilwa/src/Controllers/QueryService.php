<?php

	namespace Tilwa\Controllers;

	abstract class QueryService extends BaseQueryInterceptor {

		// @description determine [fetchModel] is authorized to be viewed
		abstract public function shouldFetch(object $fetchModel):bool;
	}
?>