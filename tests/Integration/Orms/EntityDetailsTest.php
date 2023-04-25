<?php

namespace Suphle\Tests\Integration\Orms;

use Suphle\Contracts\Database\EntityDetails;

use Suphle\Hydration\Container;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Suphle\Tests\Mocks\Models\Eloquent\Employment;

class EntityDetailsTest extends ModuleLevelTest
{
    use BaseDatabasePopulator;

    protected function getModules(): array
    {

        return [new ModuleOneDescriptor(new Container())]; // anything involving orm requires requestDetails->events->modules
    }

    protected function getActiveEntity(): string
    {

        return Employment::class;
    }

    public function test_correctly_normalizes_identifier()
    {

        $this->dataProvider([

            $this->modelPrefixDataset(...) // given
        ], function (object $model, ?string $prefix, string $expected) {

            $sut = $this->getContainer()->getClass(EntityDetails::class);

            // when
            if (!is_null($prefix)) {

                $result = $sut->idFromModel($model, $prefix);
            } else {
                $result = $sut->idFromModel($model);
            }

            $this->assertSame($result, $expected); // then
        });
    }

    public function modelPrefixDataset(): array
    {

        $model = $this->replicator->getRandomEntity();

        $modelId = $model->id;

        return [

            [$model, null, "employment_$modelId" ],

            [$model, "prefix", "prefix_employment_$modelId" ]
        ];
    }
}
