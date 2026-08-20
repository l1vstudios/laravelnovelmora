<?php

namespace App\Http\Controllers;

use App\Models\TrxBuyKoin;
use App\Models\TrxBuyPaket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type'); // 'paket', 'koin', or null (all)
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query trx_buy_paket
        $paketQuery = DB::table('trx_buy_paket')
            ->join('mst_users', 'trx_buy_paket.user_id', '=', 'mst_users.id')
            ->select(
                'trx_buy_paket.id',
                DB::raw("'paket' as type"),
                'trx_buy_paket.user_id',
                'mst_users.name as user_name',
                'mst_users.email as user_email',
                'trx_buy_paket.transaction_id',
                'trx_buy_paket.nama_paket as description',
                DB::raw("CAST(trx_buy_paket.amount_price AS INTEGER) as amount_price"),
                'trx_buy_paket.start_date',
                'trx_buy_paket.end_date',
                'trx_buy_paket.status_payment',
                'trx_buy_paket.created_at'
            );

        // Query trx_buy_koin
        $koinQuery = DB::table('trx_buy_koin')
            ->join('mst_users', 'trx_buy_koin.user_id', '=', 'mst_users.id')
            ->select(
                'trx_buy_koin.id',
                DB::raw("'koin' as type"),
                'trx_buy_koin.user_id',
                'mst_users.name as user_name',
                'mst_users.email as user_email',
                'trx_buy_koin.transaction_id',
                DB::raw("CONCAT(trx_buy_koin.amount_koin, ' Koin') as description"),
                'trx_buy_koin.amount_price',
                DB::raw("NULL as start_date"),
                DB::raw("NULL as end_date"),
                'trx_buy_koin.status_payment',
                'trx_buy_koin.created_at'
            );

        // Apply filters
        if ($search) {
            $paketQuery->where(function ($q) use ($search) {
                $q->where('mst_users.name', 'ilike', "%{$search}%")
                  ->orWhere('mst_users.email', 'ilike', "%{$search}%")
                  ->orWhere('trx_buy_paket.transaction_id', 'ilike', "%{$search}%");
            });
            $koinQuery->where(function ($q) use ($search) {
                $q->where('mst_users.name', 'ilike', "%{$search}%")
                  ->orWhere('mst_users.email', 'ilike', "%{$search}%")
                  ->orWhere('trx_buy_koin.transaction_id', 'ilike', "%{$search}%");
            });
        }

        if ($startDate) {
            $paketQuery->whereDate('trx_buy_paket.created_at', '>=', $startDate);
            $koinQuery->whereDate('trx_buy_koin.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $paketQuery->whereDate('trx_buy_paket.created_at', '<=', $endDate);
            $koinQuery->whereDate('trx_buy_koin.created_at', '<=', $endDate);
        }

        // Filter by type
        if ($type === 'paket') {
            $combinedQuery = $paketQuery;
        } elseif ($type === 'koin') {
            $combinedQuery = $koinQuery;
        } else {
            $combinedQuery = $paketQuery->unionAll($koinQuery);
        }

        // Wrap in subquery for ordering and pagination
        $earnings = DB::table(DB::raw("({$combinedQuery->toSql()}) as earnings"))
            ->mergeBindings($combinedQuery)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Summary statistics
        $summaryPaketQuery = DB::table('trx_buy_paket')->where('status_payment', 'verified');
        $summaryKoinQuery = DB::table('trx_buy_koin')->where('status_payment', 'verified');

        if ($startDate) {
            $summaryPaketQuery->whereDate('created_at', '>=', $startDate);
            $summaryKoinQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $summaryPaketQuery->whereDate('created_at', '<=', $endDate);
            $summaryKoinQuery->whereDate('created_at', '<=', $endDate);
        }

        $totalPaket = (int) $summaryPaketQuery->sum(DB::raw("CAST(amount_price AS INTEGER)"));
        $totalKoin = (int) $summaryKoinQuery->sum('amount_price');
        $totalGross = $totalPaket + $totalKoin;
        $totalNet = (int) round($totalGross * 0.70); // After 30% Google Play commission

        $summary = [
            'total_gross' => $totalGross,
            'total_net' => $totalNet,
            'total_paket' => $totalPaket,
            'total_koin' => $totalKoin,
            'count_paket' => DB::table('trx_buy_paket')->where('status_payment', 'verified')->count(),
            'count_koin' => DB::table('trx_buy_koin')->where('status_payment', 'verified')->count(),
        ];

        return view('content.earning.index', compact('earnings', 'summary', 'type', 'search', 'startDate', 'endDate'));
    }
}
