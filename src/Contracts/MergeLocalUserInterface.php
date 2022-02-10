<?php

namespace AbcDaConstrucao\AutorizacaoCliente\Contracts;

interface MergeLocalUserInterface
{
    /**
     * Retorna os dados complementares do usuário da aplicação local para merge com os dados
     * do usuário logado na API de Autenticação/Autorização
     *
     * @param int $abcUserId id do usuário na API de Autenticação/Autorização que tenha relação com um usuário app local
     * @return array
     */
    public function getUserFromMerge(int $abcUserId): array;
}
