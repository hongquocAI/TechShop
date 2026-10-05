<?php

namespace App\Services;

use App\Models\Product;

/** Giỏ hàng lưu trong session: [product_id => quantity] */
class CartService
{
    public function raw(): array
    {
        return session('cart', []);
    }

    public function add(int $productId, int $qty = 1): void
    {
        $cart = $this->raw();
        $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
        session(['cart' => $cart]);
    }

    public function set(int $productId, int $qty): void
    {
        $cart = $this->raw();
        $qty > 0 ? $cart[$productId] = $qty : $cart = array_diff_key($cart, [$productId => 1]);
        session(['cart' => $cart]);
    }

    public function clear(): void
    {
        session()->forget('cart');
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    /** @return array{items: \Illuminate\Support\Collection, subtotal: int, shipping: int, total: int} */
    public function summary(): array
    {
        $products = Product::published()->whereIn('id', array_keys($this->raw()))->get()->keyBy('id');
        $items = collect($this->raw())->map(function ($qty, $id) use ($products) {
            $p = $products->get($id);
            return $p ? (object) ['product' => $p, 'quantity' => $qty, 'line_total' => $p->final_price * $qty] : null;
        })->filter();

        $subtotal = (int) $items->sum('line_total');
        $shipping = ($subtotal === 0 || $subtotal >= config('payment.free_ship_from')) ? 0 : config('payment.shipping_fee');

        return ['items' => $items, 'subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping];
    }
}
