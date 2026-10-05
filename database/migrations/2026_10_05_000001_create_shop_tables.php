<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('customer')->after('password'); // customer | admin
            $t->string('phone')->nullable()->after('role');
        });

        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });

        Schema::create('brands', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });

        // Thuộc tính động (PIM): mỗi danh mục có bộ thuộc tính riêng
        Schema::create('attributes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->string('name');                 // VD: Dung lượng pin
            $t->string('code');                 // VD: battery
            $t->string('type')->default('text'); // text | number | select
            $t->json('options')->nullable();    // danh sách lựa chọn khi type = select
            $t->boolean('is_required')->default(false);
            $t->boolean('is_filterable')->default(false);
            $t->timestamps();
            $t->unique(['category_id', 'code']);
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained();
            $t->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('sku')->unique();
            $t->unsignedBigInteger('price');
            $t->unsignedBigInteger('sale_price')->nullable();
            $t->unsignedInteger('stock')->default(0);
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->string('status')->default('draft'); // draft | review | published
            $t->unsignedTinyInteger('completeness')->default(0);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('product_attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $t->string('value');
            $t->unique(['product_id', 'attribute_id']);
        });

        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('phone');
            $t->string('address');
            $t->string('note')->nullable();
            $t->unsignedBigInteger('subtotal');
            $t->unsignedBigInteger('shipping_fee')->default(0);
            $t->unsignedBigInteger('total');
            $t->string('payment_method');                  // cod | vnpay
            $t->string('payment_status')->default('unpaid'); // unpaid | paid | failed
            $t->string('status')->default('pending');        // pending | confirmed | shipping | completed | cancelled
            $t->timestamps();
        });

        // Snapshot: lưu lại tên/giá lúc mua
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->string('sku');
            $t->unsignedBigInteger('price');
            $t->unsignedInteger('quantity');
        });

        Schema::create('payment_transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->string('gateway');                       // cod | vnpay | mock
            $t->string('transaction_code')->unique();    // vnp_TxnRef -> chống xử lý trùng
            $t->unsignedBigInteger('amount');
            $t->string('status')->default('pending');    // pending | success | failed
            $t->string('gateway_transaction_no')->nullable();
            $t->json('raw_response')->nullable();        // log toàn bộ callback
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['payment_transactions', 'order_items', 'orders', 'product_attribute_values', 'products', 'attributes', 'brands', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'phone']));
    }
};
