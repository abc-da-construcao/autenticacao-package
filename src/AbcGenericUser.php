<?php

namespace AbcDaConstrucao\AutenticacaoPackage;

use Illuminate\Auth\GenericUser;

class AbcGenericUser extends GenericUser
{
    protected $primaryKeyName = 'id';

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->primaryKeyName;
    }

    /**
     * Object to Array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * @return object
     */
    public function toObject()
    {
        return (object)$this->attributes;
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->attributes);
    }
}
