<?php
	namespace Suphle\Request;

	use Suphle\Contracts\Auth\AuthStorage;

	abstract class RouteRule {

		public function __construct(protected AuthStorage $authStorage)
  {
  }

		abstract public function permit ():bool;
	}
?>