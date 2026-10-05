<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    public function __construct(private CartService $cart) {}

    /**
     * Đặt hàng an toàn: transaction + khóa dòng sản phẩm (lockForUpdate)
     * để 2 người mua cùng lúc không làm tồn kho bị âm.
     */
    public function place(array $data, ?int $userId): Order
    {
        $summary = $this->cart->summary();
        if ($summary['items']->isEmpty()) {
            throw new RuntimeException('Giỏ hàng trống.');
        }

        return DB::transaction(function () use ($data, $userId, $summary) {
            $order = Order::create([
                'code' => 'DH'.now()->format('ymd').strtoupper(Str::random(5)),
                'user_id' => $userId,
                'name' => $data['name'], 'phone' => $data['phone'],
                'address' => $data['address'], 'note' => $data['note'] ?? null,
                'subtotal' => $summary['subtotal'], 'shipping_fee' => $summary['shipping'],
                'total' => $summary['total'], 'payment_method' => $data['payment_method'],
            ]);

            foreach ($summary['items'] as $line) {
                $product = Product::lockForUpdate()->find($line->product->id);
                if ($product->stock < $line->quantity) {
                    throw new RuntimeException("Sản phẩm \"{$product->name}\" chỉ còn {$product->stock} cái.");
                }
                $product->decrement('stock', $line->quantity);

                $order->items()->create([
                    'product_id' => $product->id, 'name' => $product->name, 'sku' => $product->sku,
                    'price' => $product->final_price, 'quantity' => $line->quantity,
                ]);
            }

            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => $data['payment_method'],
                'transaction_code' => $order->code.'-'.now()->format('His'),
                'amount' => $order->total,
            ]);

            return $order;
        });
    }
}
