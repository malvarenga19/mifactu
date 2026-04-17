<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductEquivalent extends Model
{
    protected $fillable = ['product_id', 'equivalent_code'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    
}
