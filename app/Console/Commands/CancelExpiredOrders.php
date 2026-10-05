<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Hủy đơn thanh toán online quá hạn và hoàn lại tồn kho';

    public function handle(): int
    {
        $orders = Order::where('payment_method', 'vnpay')->where('payment_status', 'unpaid')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(config('payment.expire_minutes')))->get();

        foreach ($orders as $order) {
            $order->cancel();
            $order->transactions()->where('status', 'pending')->update(['status' => 'failed']);
        }
        $this->info("Đã hủy {$orders->count()} đơn quá hạn.");

        return self::SUCCESS;
    }
}
