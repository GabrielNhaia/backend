<?php
namespace App\Services;

class ExpireUsersService
{
    public function execute(): int
    {
        return app(ExpirarUsuariosService::class)->executar();
    }
}