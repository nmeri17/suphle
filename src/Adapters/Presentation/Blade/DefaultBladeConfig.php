<?php
	namespace Suphle\Adapters\Presentation\Blade;

	use Suphle\Contracts\Config\{ModuleFiles, Blade as BladeConfig};

	use Suphle\Contracts\Bridge\LaravelContainer;

	use Illuminate\{Filesystem\Filesystem, Events\Dispatcher};

	use Illuminate\View\Engines\{EngineResolver, CompilerEngine};

	use Illuminate\View\{FileViewFinder, Factory as BladeViewFactory, Compilers\BladeCompiler};

	use Illuminate\Support\Facades\{ View as ViewFacade, Blade as BladeFacade};

	use Illuminate\Contracts\View\Factory as BladeViewFactoryInterface;

	/**
	 * Using a config for this instead of an interface loader since there's opportunity for it to be called from the parser
	 * 
	 * An interface loader will cause only one instance to exist, which is undesirable since we don't have a fixed list of view paths, which the viewFactory requires predetermined
	*/
	class DefaultBladeConfig implements BladeConfig {

		protected BladeViewFactoryInterface $viewFactory;

		protected array $viewPaths;

		public function __construct (

			protected readonly LaravelContainer $laravelContainer,

			protected readonly ModuleFiles $fileConfig
		) {

			$this->viewPaths = [$fileConfig->defaultViewPath()];
		}

		/**
		 * {@inheritdoc}
		*/
		public function addViewPath (string $markupPath):void {

			$this->viewPaths[] = $markupPath;
		}

		public function getViewFactory ():BladeViewFactoryInterface {

			return $this->viewFactory;
		}

		public function setViewFactory ():void {

			$filesystem = new Filesystem;

			$bladeCompiler = $this->getBladeCompiler($filesystem);

			$this->viewFactory = new BladeViewFactory(

				$this->getViewResolver($bladeCompiler),

				new FileViewFinder($filesystem, $this->viewPaths),

				$this->laravelContainer->make(Dispatcher::class)
			);

			$this->bindInstancesToLaravelContainer($bladeCompiler);
		}

		protected function getBladeCompiler (Filesystem $fileSystem):BladeCompiler {

			return new BladeCompiler(

				$fileSystem,

				$this->fileConfig->activeModulePath() . "compiled-views"
			);
		}

		protected function getViewResolver (BladeCompiler $bladeCompiler):EngineResolver {

			$viewResolver = new EngineResolver;

			$viewResolver->register("blade", function () use ($bladeCompiler) {

				return new CompilerEngine($bladeCompiler);
			});

			return $viewResolver;
		}

		// not really necessary but to avoid any of the objects trying to pull something from somewhere without knowing configured copies are available here
		protected function bindInstancesToLaravelContainer (BladeCompiler $bladeCompiler):void {

			$this->viewFactory->setContainer($this->laravelContainer);
			
			$this->laravelContainer->instance(

				BladeViewFactoryInterface::class, $this->viewFactory
			);

			$this->laravelContainer->alias(
				BladeViewFactoryInterface::class,

				(new class extends ViewFacade {

					public static function getFacadeAccessor() {

						return parent::getFacadeAccessor(); }
				})::getFacadeAccessor()
			);

			$this->laravelContainer->instance(

				$bladeCompiler::class, $bladeCompiler
			);

			$this->laravelContainer->alias(
				$bladeCompiler::class,

				(new class extends BladeFacade {

					public static function getFacadeAccessor() {

						return parent::getFacadeAccessor(); }
				})::getFacadeAccessor()
			);
		}
	}
?>