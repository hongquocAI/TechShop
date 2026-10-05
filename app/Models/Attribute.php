<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['category_id', 'name', 'code', 'type', 'options', 'is_required', 'is_filterable'];
    protected $casts = ['options' => 'array', 'is_required' => 'boolean', 'is_filterable' => 'boolean'];

    public function category() { return $this->belongsTo(Category::class); }
}
