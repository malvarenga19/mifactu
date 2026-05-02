<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'invoice_type_id',
        'generation_code',
        'correlative',
        'mh_stamp',
        'issue_date',
        'payment_method',
        'payment_status',
        'due_date',
        'monto_exento',
        'monto_gravado',
        'monto_iva',
        'iva_retenido',
        'isr_retenido',
        'subtotal',
        'total_amount',
        'status',
        'status_mh',
        'note',
        'mh_request',
        'mh_response',
        'mh_cancellation_request',
        'mh_cancellation_response',
        'cancellation_reason',
        'cancellation_date',
        'cancellation_mh_stamp',
        'cancellation_generation_code',
        'credit_days',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'cancellation_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoiceType()
    {
        return $this->belongsTo(InvoiceType::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
