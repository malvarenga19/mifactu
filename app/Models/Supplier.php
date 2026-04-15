<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'document_number',
        'email',
        'phone',
        'address',
        'country_id',
        'municipality_id',
        'activity_id',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function economicActivity()
    {
        return $this->belongsTo(EconomicActivity::class, 'activity_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
