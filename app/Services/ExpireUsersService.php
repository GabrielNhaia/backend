<?php
namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireUsersService
{
    public function execute(): int
    {
        $count = User::where('status', 'active')
            ->whereDate('expired_date', '<', Carbon::today())
            ->update(['status' => 'expired']);

        Log::info(sprintf(
            '[%s] ExpireUsersJob: %d users expired',
            Carbon::today()->toDateString(),
            $count
        ));

        return $count;
    }
}