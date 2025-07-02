<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'product_id','quantity','customer_id','total_price','sale_group_id', 'origin_municipality', 'destination_municipality'
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

protected static function booted()
{
    static::deleted(function ($sale) {
        // Forzar recálculo del caché de stock
        \Cache::forget("product_stock_{$sale->product_id}");
    });
}
// Relación con municipio de origen (donde está el producto)
public function originMunicipality()
{
    return $this->belongsTo(Municipality::class, 'origin_municipality');
}

// Relación con municipio de destino (donde se envía)
public function destinationMunicipality()
{
    return $this->belongsTo(Municipality::class, 'destination_municipality');
}

// Añade este método para facilitar las consultas
public function scopeTransfers($query)
{
    return $query->where('sale_type', 'transfer')
                ->whereColumn('origin_municipality', '!=', 'destination_municipality');
}

// Y este para ventas locales
public function scopeLocalSales($query)
{
    return $query->where('sale_type', 'local')
                ->orWhereColumn('origin_municipality', 'destination_municipality');
}
}
