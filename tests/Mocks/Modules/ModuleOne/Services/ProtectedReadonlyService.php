<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Services;

use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\ARequiresBCounter;

class ProtectedReadonlyService extends SystemModelEditMock1 {

	public function __construct(protected readonly ARequiresBCounter $aRequires) {}
}