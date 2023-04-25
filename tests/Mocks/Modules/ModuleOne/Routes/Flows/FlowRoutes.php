<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Flows;

use Suphle\Flows\ControllerFlows;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\FlowCoordinator;

#[HandlingCoordinator(FlowCoordinator::class)]
class FlowRoutes extends BaseCollection
{
    public function POSTS_id()
    {

        $this->_httpGet(new Json("getPostDetails"));
    }

    public function FLOW__WITH__FLOWh_id()
    {

        $renderer = new Json("parentFlow");

        $flow = new ControllerFlows();

        $flow->linksTo(
            "internal-flow/id",
            $flow

            ->previousResponse()->collectionNode("anchor")->pipeTo()
        );

        $this->_httpGet($renderer->setFlow($flow));
    }

    public function INTERNAL__FLOWh_id()
    {

        $this->_httpGet(new Json("handleChildFlow"));
    }

    public function FLOW__TO__MODULE3h()
    {

        $renderer = new Json("getsTenModels");

        $flow = new ControllerFlows();

        $flow->linksTo(
            "module-three/id",
            $flow

            ->previousResponse()->collectionNode("anchor")->pipeTo()
        );

        $this->_httpGet($renderer->setFlow($flow));
    }
}
