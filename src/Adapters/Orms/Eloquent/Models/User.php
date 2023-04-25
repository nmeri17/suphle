<?php

namespace Suphle\Adapters\Orms\Eloquent\Models;

use Suphle\Contracts\Auth\UserContract;

abstract class User extends BaseModel implements UserContract
{ // not a component template since it should be extended rather than overwritten

    /**
     * Their model doesn't type these properties
    */
    protected $hidden = ["password"];

    protected $table = "users";

    protected $guarded = ["id", "password"];

    public function getId()
    {

        return $this->id;
    }

    public function setId($id): void
    {

        $this->id = $id;
    }

    public function getPassword()
    {

        return $this->password;
    }

    public function findByPrimaryKey($id, $columns = ['*'])
    {

        return $this->find($id, $columns);
    }
}
