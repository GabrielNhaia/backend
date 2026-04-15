<?php

namespace App\Services;

use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpirarUsuariosService
{
    public function executar(): int
    {
        $quantidade = Usuario::query()
            ->where('status', 'ativo')
            ->whereDate('data_expiracao', '<', Carbon::today())
            ->update(['status' => 'expirado']);

        Log::info(sprintf(
            '[%s] ExpirarUsuariosJob: %d usuários expirados',
            Carbon::today()->toDateString(),
            $quantidade
        ));

        return $quantidade;
    }
}