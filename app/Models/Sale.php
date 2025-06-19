<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'product_id','quantity','customer_id','total_price','sale_group_id', 'date', 'ubicacion'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function purchase(){
        return $this->belongsTo(Purchase::class);
    }
    public function customer()
{
    return $this->belongsTo(Customer::class);
}
// En el modelo Sale.php
protected static function booted()
{
    static::deleted(function ($sale) {
        // Forzar recálculo del caché de stock
        \Cache::forget("product_stock_{$sale->product_id}");
    });
}
}
