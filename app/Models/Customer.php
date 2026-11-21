<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'customer_type',
        'name',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'tax_number',
        'address',
        'credit_limit',
        'payment_terms',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
