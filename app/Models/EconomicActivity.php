<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EconomicActivity extends Model
{
    protected $fillable = ['description', 'code'];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'activity_id');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'activity_id');
    }

    
}
