<?php

	namespace Tilwa\Controllers;

	use Tilwa\Contracts\PermissibleService;

	abstract class CommandService extends BaseQueryInterceptor implements PermissibleService {
	}
?>