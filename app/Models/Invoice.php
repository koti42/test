<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'pdf_path',
        'status',
        'shipping_no',
        'warehouse',
        'shipping_region',
        'address',
        'shipping_date',
        'total_amount',
        'kdv_amount',
        'currency',
        'membership_status'
    ];
}
