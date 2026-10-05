<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['order_id', 'gateway', 'transaction_code', 'amount', 'status',
        'gateway_transaction_no', 'raw_response', 'paid_at'];
    protected $casts = ['raw_response' => 'array', 'paid_at' => 'datetime'];

    public function order() { return $this->belongsTo(Order::class); }

    /** Đã có kết quả cuối cùng -> không xử lý lại (idempotent) */
    public function isFinal(): bool
    {
        return in_array($this->status, ['success', 'failed'], true);
    }
}
