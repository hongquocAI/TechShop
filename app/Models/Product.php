<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft' => 'Nháp', 'review' => 'Chờ duyệt', 'published' => 'Đang bán'];

    protected $fillable = ['category_id', 'brand_id', 'name', 'slug', 'sku', 'price', 'sale_price', 'stock',
        'description', 'image', 'status', 'completeness'];

    public function category() { return $this->belongsTo(Category::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function attributeValues() { return $this->hasMany(ProductAttributeValue::class); }

    public function scopePublished($q) { return $q->where('status', 'published'); }

    /** Giá bán thực tế (ưu tiên giá khuyến mãi) */
    public function getFinalPriceAttribute(): int
    {
        return $this->sale_price && $this->sale_price < $this->price ? $this->sale_price : $this->price;
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return 'https://placehold.co/600x600?text='.urlencode($this->name);
        }
        return str_starts_with($this->image, 'http') ? $this->image : asset('storage/'.$this->image);
    }

    /**
     * Điểm hoàn thiện dữ liệu (đặc trưng của PIM):
     * tỉ lệ các trường bắt buộc + thuộc tính bắt buộc đã được điền.
     */
    public function calculateCompleteness(): int
    {
        $checks = [
            filled($this->name), filled($this->description), filled($this->image),
            $this->price > 0, filled($this->brand_id),
        ];

        $required = Attribute::where('category_id', $this->category_id)->where('is_required', true)->pluck('id');
        $filled = $this->attributeValues()->whereIn('attribute_id', $required)->where('value', '!=', '')->pluck('attribute_id');
        foreach ($required as $id) {
            $checks[] = $filled->contains($id);
        }

        return (int) round(count(array_filter($checks)) / count($checks) * 100);
    }
}
