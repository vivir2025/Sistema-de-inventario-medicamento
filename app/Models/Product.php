<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'purchase_id','price','category_id',
        'discount','description',
    ];

    public function purchase(){
        return $this->belongsTo(Purchase::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
   // Reemplaza la relación sales() por esta versión más confiable:
public function sales()
{
    return $this->hasMany(Sale::class);
}

// Y modifica el accesor así:
public function getAvailableQuantityAttribute()
{
    if (!$this->relationLoaded('purchase')) {
        $this->load('purchase');
    }
    
    if (!$this->purchase) return 0;
    
    return $this->purchase->quantity - $this->sales()->sum('quantity');
}
}
