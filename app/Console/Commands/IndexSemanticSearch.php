<?php

namespace App\Console\Commands;

use App\Services\SemanticCatalog;
use Illuminate\Console\Command;

class IndexSemanticSearch extends Command
{
    protected $signature = 'search:index';
    protected $description = 'Xuất sản phẩm đang bán để dịch vụ tìm kiếm ngữ nghĩa tạo vector nền';

    public function handle(SemanticCatalog $catalog): int
    {
        $count = $catalog->export();
        $this->info("Đã xuất {$count} sản phẩm. Dịch vụ tìm kiếm sẽ tự cập nhật vector trong nền.");

        return self::SUCCESS;
    }
}
