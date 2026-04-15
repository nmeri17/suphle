<?php
namespace Suphle\Routing;

use Suphle\Contracts\{Presentation\BaseRenderer, Modules\HighLevelRequestHandler, Modules\DescriptorInterface};

use Suphle\Request\RequestDetails;
use Suphle\Routing\{RouteInfoExecutor, Structures\RouteInfo};
use Suphle\Modules\Structures\ActiveDescriptors;
use Suphle\Exception\Explosives\NotFoundException;

class ModuleRequestRouter implements HighLevelRequestHandler
{
    protected ?RouteInfo $foundRoute;

    protected ?BaseRenderer $renderer;

    protected DescriptorInterface $descriptor;

    public function __construct(
        protected readonly RequestDetails $requestDetails,
    ) {}

    public function canSetHandlingModule (array $routeList):bool {

        $this->foundRoute = $this->getRouteInfo(
            $routeList, $this->requestDetails->getPath(),

            $this->requestDetails->getMethod()
        );

        return !is_null($this->foundRoute);
    }

    protected function getRouteInfo(array $routeList, string $path, string $method): ?RouteInfo {

        foreach ($routeList as $details) {
            $routeInfo = new RouteInfo(
                path: $details["path"],
                method: $details["method"], 
                controllerClass: $details["coordinator"],
                controllerMethod: $details["handler"],
                preMiddlewares: $details["pre_middleware"],
                middlewares: $details["middleware"],
                moduleName: $details["module_name"],
                viewName: $details["view_name"]
            );

            if ($routeInfo->matches($path, $method)) {
                return $routeInfo;
            }
        }
        return null;
    }

    public function triggerInfoModule (ActiveDescriptors $descriptorsList):BaseRenderer {

        $this->descriptor = $descriptorsList->findMatchingExports($this->foundRoute->moduleName);

        return $this->renderer = $this->descriptor->getContainer()

        ->getClass(RouteInfoExecutor::class) // both can't coexist on same class cuz those guy's deps should come relevant container

        ->handleFoundRoute($this->foundRoute);
    }
    
    public function handlingRenderer(): ?BaseRenderer {

        return $this->renderer;
    }
    public function getActiveModule():DescriptorInterface {

        return $this->descriptor;
    }
}