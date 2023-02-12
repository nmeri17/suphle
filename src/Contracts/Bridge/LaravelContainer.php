<?php
	namespace Suphle\Contracts\Bridge;

	use Suphle\Contracts\Hydration\ClassHydrationBehavior;

	use Suphle\Request\{RequestDetails, PayloadStorage};

	use Illuminate\Http\Request as LaravelRequest;

	interface LaravelContainer extends ClassHydrationBehavior {

		public const INCOMING_REQUEST_KEY = "request";

		/**
		 * @return array
		*/
		public function getBindings();

		/**
		 * @return void
		*/
		public function bind($abstract, $concrete = null, $shared = false);

		public function make($abstract, array $parameters = []);

		public function instance($abstract, $instance);

		public function basePath($path = "");

		public function concreteBinds ():array;

		public function simpleBinds ():array;

		public function registerConcreteBindings (array $bindings):void;

		public function registerSimpleBindings (array $bindings):void;

		public function runContainerBootstrappers ():void;

		public function overrideAppHelper ():void;

		public function provideRequest (

			RequestDetails $requestDetails, PayloadStorage $payloadStorage
		):LaravelRequest;
	}
?>