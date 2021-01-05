<?php

	namespace Tilwa\App;

	abstract class ServiceProvider {

		public function bindArguments();

		// call methods on the initialized object, maybe to configure/prepare it for use
		public function afterBind();

		abstract public function concrete():string;
	}
?>