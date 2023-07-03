<?php

namespace Suphle\Adapters\Presentation\Blade;

use Suphle\Contracts\{Config\ModuleFiles, Bridge\LaravelContainer};

use Suphle\Contracts\Presentation\{HtmlParser, RendersMarkup};

use Suphle\Services\Decorators\BindsAsSingleton;

use Illuminate\{Filesystem\Filesystem, Events\Dispatcher};

use Illuminate\View\Engines\{EngineResolver, CompilerEngine};

use Illuminate\View\{FileViewFinder, Factory as BladeViewFactory, Compilers\BladeCompiler};

use Illuminate\Support\Facades\{ View as ViewFacade, Blade as BladeFacade};

use Illuminate\Contracts\View\Factory as BladeViewFactoryInterface;

/**
 * Not binding this by default since it can't register any layout bindings, thus it can't load any error pages when the main/intended request fails
*/
#[BindsAsSingleton(HtmlParser::class)]
class DefaultBladeAdapter implements HtmlParser
{
    protected BladeViewFactoryInterface $viewFactory;

    protected BladeCompiler $bladeCompiler;

    protected array $viewPaths;

    public function __construct(
        protected readonly LaravelContainer $laravelContainer,
        protected readonly ModuleFiles $fileConfig
    ) {

        $this->viewPaths = [$fileConfig->defaultViewPath()];
    }

    public function findInPath(string $markupPath): void
    {

        $this->viewPaths[] = $markupPath; // Where present, this must be called before setViewFactory
    }

    public function crudFilesLocation(): string
    {

        return __DIR__ . DIRECTORY_SEPARATOR. "CrudTemplates". DIRECTORY_SEPARATOR;
    }

    public function parseRenderer(RendersMarkup $renderer): string
    {

        $this->setViewFactory(); // these calls ought to reside in an interface loader but if they're called before all paths are being set, the factory won't include those sources

        $this->bindComponentTags();

        return $this->parseRaw(
            $renderer->getMarkupName(),
            $renderer->getRawResponse()
        );
    }

    /**
     * This can't exist on the interface cuz different engines will want different sets of arguments. Thus it differs from adapter to adapter
    */
    public function parseRaw(string $markupName, iterable $payload): string
    {

        return $this->viewFactory->make($markupName, $payload)

        ->render();
    }

    public function bindComponentTags(): void
    {
    }

    public function setViewFactory(): void
    {

        $filesystem = new Filesystem();

        $this->setBladeCompiler($filesystem);

        $this->viewFactory = new BladeViewFactory(
            $this->getViewResolver(),
            new FileViewFinder($filesystem, $this->viewPaths),
            $this->laravelContainer->make(Dispatcher::class)
        );

        $this->bindInstancesToLaravelContainer();
    }

    protected function setBladeCompiler(Filesystem $fileSystem): void
    {

        $this->bladeCompiler = new BladeCompiler(
            $fileSystem,
            $this->fileConfig->activeModulePath() . "compiled-views"
        );
    }

    protected function getViewResolver(): EngineResolver
    {

        $viewResolver = new EngineResolver();

        $viewResolver->register("blade", function () {

            return new CompilerEngine($this->bladeCompiler);
        });

        return $viewResolver;
    }

    // not really necessary but to avoid any of the objects trying to pull something from somewhere without knowing configured copies are available here
    protected function bindInstancesToLaravelContainer(): void
    {

        $this->viewFactory->setContainer($this->laravelContainer);

        $this->laravelContainer->instance(
            BladeViewFactoryInterface::class,
            $this->viewFactory
        );

        $this->laravelContainer->alias(
            BladeViewFactoryInterface::class,
            (new class () extends ViewFacade {
                public static function getFacadeAccessor()
                {

                    return parent::getFacadeAccessor();
                }
            })::getFacadeAccessor()
        );

        $this->laravelContainer::setInstance($this->laravelContainer); // without this, the call creates a fresh container without all the bindings above

        $this->bladeCompiler->anonymousComponentPath(
            $this->fileConfig->defaultViewPath() // requires presence of a composer.json with at least one psr4 namespace for guessing namespace to use
        );

        $this->laravelContainer->instance(
            BladeCompiler::class,
            $this->bladeCompiler
        );

        $this->laravelContainer->alias(
            BladeCompiler::class,
            (new class () extends BladeFacade {
                public static function getFacadeAccessor()
                {

                    return parent::getFacadeAccessor();
                }
            })::getFacadeAccessor()
        );
    }
}
