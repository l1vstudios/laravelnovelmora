<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxBuyPaket extends Model
{
    protected $table = 'trx_buy_paket';

    protected $fillable = [
        'user_id',
        'transaction_id',
        'amount_price',
        'nama_paket',
        'status_payment',
        'start_date',
        'end_date',
        'count_daily',
        'status',
        'purchase_token',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(MstUser::class, 'user_id');
    }
}
