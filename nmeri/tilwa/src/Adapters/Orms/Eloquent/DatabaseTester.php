<?php
	namespace Tilwa\Adapters\Orms\Eloquent;

	use Tilwa\Contracts\Database\{OrmTester, OrmDialect};

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use PHPUnit\Framework\TestCase;

	use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;

	/**
	 * Extending TestCase to make [$this->assertThat] available
	*/
	class DatabaseTester extends TestCase implements OrmTester {

		use InteractsWithDatabase;

		protected $app;

		/**
		 * @param {ormDialect} Trigger hydration of this for it to get bound for later use in $app, thereby taking that burden off the consumer
		*/
		public function __construct (LaravelContainer $laravelContainer, OrmDialect $ormDialect) {

			$this->app = $laravelContainer;
		}

		public function __call (string $methodName, array $arguments) {

			return $this->$methodName(...$arguments); // instead of manually changing accessibility on underlying client
		}
	}
?>