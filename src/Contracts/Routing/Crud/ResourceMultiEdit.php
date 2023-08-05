<?php

namespace Suphle\Contracts\Routing\Crud;

use Suphle\Contracts\Services\CallInterceptors\MultiUserModelEdit;

interface ResourceMultiEdit extends MultiUserModelEdit {

	public function createSingle (array $modelProperties):object;

	public function deleteById (string $id):bool;

	public function paginate ( int $limit = null):iterable;
}