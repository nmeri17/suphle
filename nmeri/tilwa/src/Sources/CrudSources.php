<?php

	namespace Tilwa\Sources;

	use Controllers\Bootstrap;

	/**
	 * feel free to add validator as desired
	 **/
	class CrudSources extends BaseSource {

		protected $app;

		protected $model;

		function __construct(Bootstrap $app, string $modelName) {
			
			$this->app = $app;

			$this->model = $app->getClass($modelName);
		}

		function showCreateForm ( array $reqData, array $reqPlaceholders, array $validationErrors) {}

		function create ( array $reqData, array $reqPlaceholders, array $validationErrors) {}

		function showAll ( array $reqData, array $reqPlaceholders, array $validationErrors) {}

		// all methods redirect to this
		function showOne ( array $reqData, array $reqPlaceholders, array $validationErrors) {}

		function update ( array $reqData, array $reqPlaceholders, array $validationErrors) {}

		function delete ( array $reqData, array $reqPlaceholders, array $validationErrors) {}
	}
?>