<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Services;

use Suphle\Contracts\Services\Models\IntegrityModel;

use Suphle\Services\Structures\BaseMultiUserModelEdit;

class EmploymentEditMock extends BaseMultiUserModelEdit {

    public function getResource(object $builder): IntegrityModel
    {

        return $builder->first();
    }

    public function updateResource(object $builder, array $toUpdate)
    {

        return $builder->update($toUpdate);
    }
}
