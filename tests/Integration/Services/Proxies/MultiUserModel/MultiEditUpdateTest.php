<?php

namespace Suphle\Tests\Integration\Services\Proxies\MultiUserModel;

use Suphle\Services\DecoratorHandlers\MultiUserEditHandler;

use Suphle\Contracts\Services\Models\IntegrityModel;

use Suphle\Exception\Explosives\EditIntegrityException;

use Suphle\Testing\{TestTypes\ModuleLevelTest, Condiments\BaseDatabasePopulator};

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{EmploymentEditMock, EmploymentEditError};

use Suphle\Tests\Mocks\Models\Eloquent\Employment;

use Suphle\Tests\Integration\Services\ReplacesRequestPayload;

use DateTime;
use DateInterval;

class MultiEditUpdateTest extends ModuleLevelTest
{
    use BaseDatabasePopulator, ReplacesRequestPayload {

        BaseDatabasePopulator::setUp as databaseAllSetup;
    }

    private Employment $lastInserted;

    private string $modelName = Employment::class;

    private string $sutName = EmploymentEditMock::class;

    protected function setUp(): void
    {
    	$this->databaseAllSetup();

        $this->lastInserted = $this->replicator->getRandomEntity();
    }

    public function test_missing_key_on_update_throws_error()
    {

        $this->expectException(EditIntegrityException::class); // then

        $this->stubRequestObjects(5);

        $this->updateSutResource($this->getContainer()->getClass($this->sutName), [

        	"id" => 5
        ]); // when
    }

    protected function getActiveEntity(): string
    {

        return $this->modelName;
    }

    public function test_last_updater_invalidates_for_all_viewers()
    {

        $this->expectException(EditIntegrityException::class); // then

        $threeMinutesAgo = (new DateTime())->sub(new DateInterval("PT3M"))

        ->format(MultiUserEditHandler::DATE_FORMAT);

        // given
        $modelId = $this->lastInserted->id;

        $payload = [

            MultiUserEditHandler::INTEGRITY_KEY => $threeMinutesAgo,

            "name" => "ujunwa", "id" => $modelId
        ];

        $this->stubRequestObjects($modelId, $payload);

        // when
        $sut = $this->getContainer()->getClass($this->sutName); // to wrap in decorator

        for ($i = 0; $i < 2; $i++) { // first request updates integrityKey. Next iteration should fail
            
            $this->updateSutResource($sut, $payload);
        }
    }

    public function test_update_can_withstand_errors()
    {

        $columnName = IntegrityModel::INTEGRITY_COLUMN;

        $modelId = $this->lastInserted->id;

        $payload = [

            MultiUserEditHandler::INTEGRITY_KEY => $this->lastInserted->$columnName,

            "name" => "ujunwa", "id" => $modelId
        ];

        $this->stubRequestObjects($modelId, $payload);

        $result = $this->updateSutResource(
        
        	$this->getContainer()->getClass(EmploymentEditError::class), $payload
        ); // when

        $this->assertSame("boo!", $result); // then
    }

    /**
     * @return call result
    */
    protected function updateSutResource (EmploymentEditMock $sut, array $payload = []) {

    	return $sut->updateResource($this->lastInserted, $payload);
    }
}
