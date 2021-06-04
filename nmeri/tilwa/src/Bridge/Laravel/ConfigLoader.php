<?php
	namespace Tilwa\Bridge\Laravel;

	use Illuminate\Config\Repository;

	/* in the laravel app provider, we
	->bind(Transistor::class, function ($app) {
	    return new ConfigLoader;
	});
	we also wanna call Illuminate\Foundation\Bootstrap\LoadConfiguration->bootstrap immediately after app is instanciated
	*/
	class ConfigLoader extends Repository { // continue here

		// label the key (paystack.come {during retrieval, recursively split by dots}) for its config class [paystack => Config\Paystack::class]
		public function get($key, $default = null) {

	        if (is_array($key)) {
	            return $this->getMany($key);
	        }

	        // check the injected config map. then when no response

	        return Arr::get($this->items, $key, $default);
	    }

	    // @TODO override [getMany]
	}
?>