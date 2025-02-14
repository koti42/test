<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function updateTotals(): void
    {
        $products = $this->products;
        
        $this->total_amount = $products->sum('total_price');
        $this->kdv_amount = $products->sum('tax_amount');
        
        $this->save();
    }
}
