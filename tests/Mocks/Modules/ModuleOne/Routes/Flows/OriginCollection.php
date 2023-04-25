<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Flows;

use Suphle\Routing\{BaseCollection, Decorators\HandlingCoordinator};

use Suphle\Response\Format\Json;

use Suphle\Flows\{ControllerFlows, Structures\ServiceContext};

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\FlowCoordinator;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\FlowService;

#[HandlingCoordinator(FlowCoordinator::class)]
class OriginCollection extends BaseCollection
{
    protected string $queryNodeHolder = "next_page_url";

    public function COMBINE__FLOWSh()
    {

        $renderer = new Json("handleCombined");

        $flow = new ControllerFlows();

        $flow->linksTo(
            "paged-data",
            $flow

            ->previousResponse()->getNode($this->queryNodeHolder)

            ->altersQuery()
        )
        ->linksTo(
            "categories/id",
            $flow->previousResponse()->collectionNode("data") // assumes we're coming from "/categories"

            ->pipeTo(),
        );

        $this->_httpGet($renderer->setFlow($flow));
    }

    public function SINGLE__NODEh()
    {

        $renderer = new Json("handleSingleNode");

        $flow = new ControllerFlows();

        $flow->linksTo(
            "paged-data",
            $flow

            ->previousResponse()->getNode($this->queryNodeHolder)

            ->altersQuery()
        );

        $this->_httpGet($renderer->setFlow($flow));
    }

    public function FROM__SERVICEh()
    {

        $renderer = new Json("handleFromService");

        $flow = new ControllerFlows();

        $serviceContext = new ServiceContext(FlowService::class, "customHandlePrevious");

        $flow->linksTo(
            "orders/sort/id/id2",
            $flow->previousResponse()

            ->collectionNode("store.id")

            ->setFromService($serviceContext)

            ->inRange() // has a parameterised and date variant
            // try using any other collection based method aside ranges
            // after adding them, update [flroutest->getOriginUrls]
        );

        $this->_httpGet($renderer->setFlow($flow));
    }

    public function PIPE__TOh()
    {

        $renderer = new Json("handlePipeTo");

        $flow = new ControllerFlows();

        $flow->linksTo(
            "categories/id",
            $flow->previousResponse()
            ->collectionNode("data") // assumes we're coming from "/categories"

            ->pipeTo(),
        );

        $this->_httpGet($renderer->setFlow($flow));
    }

    public function ONE__OFh()
    {

        $renderer = new Json("handleOneOf");

        $flow = new ControllerFlows();

        $flow->linksTo(
            "store/id",
            $flow->previousResponse()->collectionNode("data", "product_name")

            ->asOne()
        );

        $this->_httpGet($renderer->setFlow($flow));
    }

    public function NO__FLOWh()
    {

        $this->_httpGet(new Json("noFlowHandler"));
    }

    public function USER__CONTENTh_id()
    {

        $this->_httpGet(new Json("readFlowPayload"));
    }
}
