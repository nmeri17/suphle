<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Bridge\Laravel\Controllers;

	use Illuminate\Foundation\{Bus\DispatchesJobs, Auth\Access\AuthorizesRequests, Validation\ValidatesRequests};

	use Illuminate\Routing\Controller as BaseController;

	// replaces App\Http\Controllers\Controller, from a namespace we have no use for
	class DefaultController extends BaseController {

		use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	}
?>