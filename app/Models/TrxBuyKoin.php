<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrxBuyKoin extends Model
{
    protected $table = 'trx_buy_koin';

    protected $fillable = [
        'user_id',
        'amount_koin',
        'amount_price',
        'status_payment',
        'transaction_id',
    ];

    public function user()
    {
        return $this->belongsTo(MstUser::class, 'user_id');
    }
}
