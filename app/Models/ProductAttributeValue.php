<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id', 'attribute_id', 'value'];

    public function attribute() { return $this->belongsTo(Attribute::class); }
}
