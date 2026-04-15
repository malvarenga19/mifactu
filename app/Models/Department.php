<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
