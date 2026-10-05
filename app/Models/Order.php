<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy',
    ];

    // State machine đơn giản: trạng thái nào được chuyển sang trạng thái nào
    public const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipping', 'cancelled'],
        'shipping' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public const PAYMENT_STATUSES = ['unpaid' => 'Chưa thanh toán', 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại'];

    protected $fillable = ['code', 'user_id', 'name', 'phone', 'address', 'note', 'subtotal', 'shipping_fee',
        'total', 'payment_method', 'payment_status', 'status'];

    public function items() { return $this->hasMany(OrderItem::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(PaymentTransaction::class); }

    public function canTransitionTo(string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /** Hủy đơn và hoàn lại tồn kho */
    public function cancel(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }
        foreach ($this->items as $item) {
            Product::where('id', $item->product_id)->increment('stock', $item->quantity);
        }
        $this->update(['status' => 'cancelled']);
    }
}
