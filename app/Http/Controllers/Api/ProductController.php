<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $request->validate([
            'product_name' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
        ]);

        $total_price = $request->quantity * $request->unit_price;
        $tax_amount = ($total_price * $request->tax_rate) / 100;

        $product = $invoice->products()->create([
            'name' => $request->product_name,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'tax_rate' => $request->tax_rate,
            'total_price' => $total_price,
            'tax_amount' => $tax_amount,
        ]);

        $invoice->updateTotals();

        return response()->json($product, 201);
    }
}
