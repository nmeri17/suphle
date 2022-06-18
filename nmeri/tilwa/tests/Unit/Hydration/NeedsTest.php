<?php
	namespace Tilwa\Tests\Unit\Hydration;

	use Tilwa\Hydration\{Container, Structures\ContainerTelescope};

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Tests\Integration\Generic\CommonBinds;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\{ ARequiresBCounter, BCounter};

	class NeedsTest extends IsolatedComponentTest {

		use CommonBinds {

			concreteBinds as commonConcretes;
		}

		private $ourB, $aRequires = ARequiresBCounter::class;

		protected function setUp ():void {

			$this->ourB = new BCounter;

			parent::setUp();
		}

		protected function concreteBinds ():array {

			return array_merge($this->commonConcretes(), [

				BCounter::class => $this->ourB
			]);
		}

		public function test_raw_class_correctly_uses_needs () {

			// given @see [concreteBinds]

			$aConcrete = new ARequiresBCounter($this->ourB, "");

			$this->assertSame(
				$aConcrete->getInternalB($this->container), // when

				$this->ourB
			); // then
		}

		public function test_hydrated_class_with_getClass_reads_provision () {

			$hitsCount = 0;

			// given
			$container = $this->positiveDouble(Container::class, [

				"getDecorator" => $this->stubDecorator(),

				"getProvidedConcrete" => $this->returnCallback(function ($subject) {

					if (in_array($subject, [

						$this->aRequires, // skip stubbing so its arguments can be hydrated

						Container::class // since we'll provide it later
					])) return;

					return $this->positiveDouble($subject);
				})
			], [

				"getProvidedConcrete" => [$this->any(), [

					$this->callback(function ($subject) use (&$hitsCount) {

						if (BCounter::class == $subject) $hitsCount++;

						return true;
					})
				]] // then
			]);

			$this->bootContainer($container);

			$container->whenType($this->aRequires)

			->needsAny($this->concreteBinds());

			// when
			$container->getClass($this->aRequires)->getInternalB($container);

			$this->assertSame(1, $hitsCount);
		}

		public function test_hydrated_class_with_getClass_correctly_uses_needs () {

			// given @see [concreteBinds]

			$this->assertSame( // then
				$this->container->getClass($this->aRequires)
				
				->getInternalB($this->container), // when

				$this->ourB
			);
		}
	}
?>