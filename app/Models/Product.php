<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'cost_price',
        'sale_price',
        'stock',
        'min_stock',
        'image_path',
        'category_id',
        'supplier_id',
        'code',
        'location',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function equivalents()
    {
        return $this->hasMany(ProductEquivalent::class);
    }
}
