<?php

	namespace Tilwa\App;

	abstract class ServiceProvider {

		// type-hint any arguments needed here. If they've been previously provisioned, the concrete will be wired in
		public function bindArguments();

		// boot your object, maybe to configure/prepare it for use
		public function afterBind($initialized);

		abstract public function concrete():string;
	}
?>