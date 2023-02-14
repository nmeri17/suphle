<?php
	namespace Suphle\Adapters\Presentation\Blade;

	use Suphle\Contracts\{Config\ModuleFiles, Bridge\LaravelContainer};

	use Suphle\Contracts\Presentation\{BladeAdapter, RendersMarkup};

	use Illuminate\{Filesystem\Filesystem, Events\Dispatcher};

	use Illuminate\View\Engines\{EngineResolver, CompilerEngine};

	use Illuminate\View\{FileViewFinder, Factory as BladeViewFactory, Compilers\BladeCompiler};

	use Illuminate\Support\Facades\{ View as ViewFacade, Blade as BladeFacade};

	use Illuminate\Contracts\View\Factory as BladeViewFactoryInterface;

	class DefaultBladeAdapter implements BladeAdapter {

		protected BladeViewFactoryInterface $viewFactory;

		protected BladeCompiler $bladeCompiler;

		protected array $viewPaths;

		public function __construct (

			protected readonly LaravelContainer $laravelContainer,

			protected readonly ModuleFiles $fileConfig
		) {

			$this->viewPaths = [$fileConfig->defaultViewPath()];
		}

		public function findInPath (string $markupPath):void { // move up there

			$this->viewPaths[] = $markupPath; // Where present, this must be called before setViewFactory
		}

		public function parseAll (RendersMarkup $renderer):string {

			return $this->viewFactory->make(

				$renderer->getMarkupName(), $renderer->getRawResponse()
			)->render();
		}

		public function bindComponentTags ():void {}

		public function setViewFactory ():void {

			$filesystem = new Filesystem;

			$this->setBladeCompiler($filesystem);

			$this->viewFactory = new BladeViewFactory(

				$this->getViewResolver(),

				new FileViewFinder($filesystem, $this->viewPaths),

				$this->laravelContainer->make(Dispatcher::class)
			);

			$this->bindInstancesToLaravelContainer();
		}

		protected function setBladeCompiler (Filesystem $fileSystem):void {

			$this->bladeCompiler = new BladeCompiler(

				$fileSystem,

				$this->fileConfig->activeModulePath() . "compiled-views"
			);
		}

		protected function getViewResolver ():EngineResolver {

			$viewResolver = new EngineResolver;

			$viewResolver->register("blade", function () {

				return new CompilerEngine($this->bladeCompiler);
			});

			return $viewResolver;
		}

		// not really necessary but to avoid any of the objects trying to pull something from somewhere without knowing configured copies are available here
		protected function bindInstancesToLaravelContainer ():void {

			$this->viewFactory->setContainer($this->laravelContainer);
			
			$this->laravelContainer->instance(

				BladeViewFactoryInterface::class, $this->viewFactory
			);

			$this->laravelContainer->alias(
				BladeViewFactoryInterface::class,

				(new class extends ViewFacade {

					public static function getFacadeAccessor() {

						return parent::getFacadeAccessor();
					}
				})::getFacadeAccessor()
			);

			$this->laravelContainer->instance(

				BladeCompiler::class, $this->bladeCompiler
			);

			$this->laravelContainer->alias(
				BladeCompiler::class,

				(new class extends BladeFacade {

					public static function getFacadeAccessor() {

						return parent::getFacadeAccessor();
					}
				})::getFacadeAccessor()
			);
		}
	}
?>