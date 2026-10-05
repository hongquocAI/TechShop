<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('cart.index', $this->cart->summary());
    }

    public function add(Request $request, Product $product)
    {
        abort_unless($product->status === 'published', 404);
        $qty = max(1, (int) $request->input('quantity', 1));
        $already = $this->cart->raw()[$product->id] ?? 0;

        if ($already + $qty > $product->stock) {
            return back()->with('error', "Chỉ còn {$product->stock} sản phẩm trong kho.");
        }
        $this->cart->add($product->id, $qty);

        return redirect()->route('cart.index')->with('success', 'Đã thêm vào giỏ hàng.');
    }

    public function update(Request $request, Product $product)
    {
        $this->cart->set($product->id, min((int) $request->input('quantity'), $product->stock));

        return back();
    }

    public function remove(Product $product)
    {
        $this->cart->set($product->id, 0);

        return back();
    }
}
