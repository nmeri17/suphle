<?php

namespace Suphle\Modules;

use Suphle\Modules\Structures\ActiveDescriptors;

use Suphle\Events\ModuleLevelEvents;

use Suphle\Contracts\{Modules\DescriptorInterface, Config\ModuleFiles};

use Suphle\Services\Decorators\BindsAsSingleton;

#[BindsAsSingleton]
class ModulesBooter
{
    protected array $modules;

    public function __construct(
        protected readonly ModuleLevelEvents $eventManager
    ) {

        //
    }

    public function bootOuterModules(ActiveDescriptors $descriptorsHolder): void
    {

        $this->modules = $descriptorsHolder->getOriginalDescriptors();

        $this->recursivelyBootModuleSet($descriptorsHolder);
    }

    public function recursivelyBootModuleSet(ActiveDescriptors $descriptorsHolder): void
    {

        foreach ($descriptorsHolder->getOriginalDescriptors() as $descriptor) {

            $descriptor->warmModuleContainer();

            $descriptor->getContainer()->whenTypeAny()->needsAny([

                DescriptorInterface::class => $descriptor,

                ActiveDescriptors::class => $descriptorsHolder // before this point, any object that requires the holder has to receive it manually
            ]);

            $descriptor->prepareToRun();

            $this->recursivelyBootModuleSet(new ActiveDescriptors(
                $descriptor->getExpatriates()
            ));
        }

        $this->eventManager->bootReactiveLogger($descriptorsHolder);
    }
}
