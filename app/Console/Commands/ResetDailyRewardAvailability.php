<?php

namespace App\Console\Commands;

use App\Models\DailyRewardClaim;
use Illuminate\Console\Command;

class ResetDailyRewardAvailability extends Command
{
    protected $signature = 'reward:reset-daily {--days=45 : Jumlah hari riwayat klaim yang disimpan}';

    protected $description = 'Membersihkan riwayat klaim reward lama; ketersediaan harian tetap dihitung dari claim_date.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $deleted = DailyRewardClaim::where('claim_date', '<', now()->subDays($days)->toDateString())->delete();

        $this->info("Reward harian siap untuk hari baru. {$deleted} klaim lama dibersihkan.");

        return self::SUCCESS;
    }
}
