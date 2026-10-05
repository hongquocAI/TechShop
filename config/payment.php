<?php

return [
    // Để trống VNP_TMN_CODE => dùng cổng giả lập (mock) để demo không cần đăng ký sandbox
    'vnpay' => [
        'tmn_code' => env('VNP_TMN_CODE'),
        'hash_secret' => env('VNP_HASH_SECRET'),
        'url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNP_RETURN_URL', env('APP_URL', 'http://localhost:8000').'/payment/vnpay/return'),
    ],
    'expire_minutes' => 15,
    'shipping_fee' => 30000,
    'free_ship_from' => 500000,
];
