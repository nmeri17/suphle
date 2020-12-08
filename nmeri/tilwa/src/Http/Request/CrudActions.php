<?php

	namespace Tilwa\Http\Request;

	abstract class CrudActions {

		abstract function showCreateForm ( BaseRequest $request);

		abstract function create ( BaseRequest $request);

		abstract function showAll ( BaseRequest $request);

		abstract function showOne ( BaseRequest $request);

		abstract function update ( BaseRequest $request);

		abstract function delete ( BaseRequest $request);
	}
?>