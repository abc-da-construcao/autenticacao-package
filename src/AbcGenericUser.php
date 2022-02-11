<?php

namespace AbcDaConstrucao\AutorizacaoCliente;

use Illuminate\Auth\GenericUser;

class AbcGenericUser extends GenericUser
{
    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }
}