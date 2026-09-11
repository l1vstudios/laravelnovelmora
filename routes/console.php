<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {

    Log::info('Daily scheduler started.');

    $exitCode = Artisan::call('reward:reset-daily');

    if ($exitCode !== 0) {
        Log::error('reward:reset-daily failed.', [
            'exit_code' => $exitCode,
        ]);

        return;
    }

    Log::info('reward:reset-daily completed successfully.');

    try {
        DB::statement(
            'REFRESH MATERIALIZED VIEW CONCURRENTLY mv_cerita_preview'
        );

        Log::info('mv_cerita_preview refreshed successfully.');
    } catch (\Throwable $e) {

        Log::error('Failed to refresh mv_cerita_preview.', [
            'message' => $e->getMessage(),
        ]);
    }

})
    ->name('daily-reward-and-cerita-refresh')
    ->dailyAt('00:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();
