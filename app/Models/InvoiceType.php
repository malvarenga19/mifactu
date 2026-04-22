<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceType extends Model
{
    protected $table = 'invoice_types';
    
    protected $fillable = [
        'code',
        'name',
        'last_correlative'
    ];
    
    protected $casts = [
        'last_correlative' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Relación con facturas
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'invoice_type_id');
    }
    
    /**
     * Get formatted correlative
     */
    public function getFormattedCorrelativeAttribute(): string
    {
        return str_pad($this->last_correlative, 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get next correlative
     */
    public function getNextCorrelativeAttribute(): int
    {
        return $this->last_correlative + 1;
    }
    
    /**
     * Get formatted next correlative
     */
    public function getFormattedNextCorrelativeAttribute(): string
    {
        return str_pad($this->next_correlative, 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Scope para búsqueda
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('code', 'like', "%{$search}%")
                     ->orWhere('name', 'like', "%{$search}%");
    }
    
    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->code = strtoupper($model->code);
            $model->name = ucfirst($model->name);
        });
        
        static::updating(function ($model) {
            $model->code = strtoupper($model->code);
            $model->name = ucfirst($model->name);
        });
    }
}