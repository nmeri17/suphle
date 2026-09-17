<?php

namespace Suphle\Modules;

use Suphle\Modules\Structures\ActiveDescriptors;

use Suphle\Events\ModuleLevelEvents;

use Suphle\Contracts\{Modules\DescriptorInterface, Config\ModuleFiles};

use Suphle\Services\Decorators\BindsAsSingleton;

#[BindsAsSingleton]
class ModulesBooter
{
    protected array $modules, $bootedDescriptors = [];

    public function __construct(
        protected readonly ModuleLevelEvents $eventEmitter,

        protected readonly ActiveDescriptors $descriptorsHolder
    ) { }

    public function bootOuterModules(): void
    {

        $this->modules = $this->descriptorsHolder->getOriginalDescriptors();

        $this->recursivelyBootModuleSet($this->descriptorsHolder);

        $this->eventEmitter->bootReactiveLogger();
    }

    public function recursivelyBootModuleSet(ActiveDescriptors $descriptorsHolder): void
    {

        foreach ($descriptorsHolder->getOriginalDescriptors() as $descriptor) {

            $descriptorName = $descriptor::class;

            if (in_array($descriptorName, $this->bootedDescriptors)) continue;

            $descriptor->warmModuleContainer();

            $descriptor->getContainer()->whenTypeAny()->needsAny([

                DescriptorInterface::class => $descriptor,

                ActiveDescriptors::class => $descriptorsHolder // before this point, any object that requires the holder has to either receive it manually or its container be booted manually
            ]);

            $descriptor->prepareToRun();

            $this->bootedDescriptors[] = $descriptorName;

            $this->recursivelyBootModuleSet(new ActiveDescriptors(
                $descriptor->getExpatriates()
            ));
        }
    }
}
