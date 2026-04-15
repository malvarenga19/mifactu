<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'activity_id',
        'country_id',
        'municipality_id',
        'address',
        'email',
        'phone',
        'company_name',
        'document_number',
        'document',
        'nrc',
        'retains_iva',
    ];

    public function economicActivity()
    {
        return $this->belongsTo(EconomicActivity::class, 'activity_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function getDocumentTypeNameAttribute()
    {
        $types = [
            '13' => 'DUI',
            '36' => 'NIT',
            '03' => 'Pasaporte',
            '02' => 'Carnet de Residente',
            '37' => 'Otro',
        ];

        return $types[$this->document] ?? $this->document ?? 'N/A';
    }
}
