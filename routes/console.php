<?php

use Illuminate\Support\Facades\Schedule;

// Tự hủy đơn VNPay quá hạn thanh toán và hoàn tồn kho
Schedule::command('orders:cancel-expired')->everyFiveMinutes();
