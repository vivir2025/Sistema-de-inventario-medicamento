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
        'discount','description','municipality'
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
// Scope para filtrar por municipio
    public function scopeByMunicipality($query, $municipality)
    {
        return $query->where('municipality', $municipality);
    }

    // Obtener nombre del municipio formateado
    public function getMunicipalityNameAttribute()
    {
        $municipalities = [
            'cajibio' => 'Cajibío',
            'morales' => 'Morales',
            'piendamo' => 'Piendamó'
        ];

        return $municipalities[$this->municipality] ?? $this->municipality;
    }


    public static function transferStock($productId, $fromMunicipality, $toMunicipality, $quantity)
{
    DB::transaction(function () use ($productId, $fromMunicipality, $toMunicipality, $quantity) {
        // 1. Descontar del producto en el municipio origen
        $originProduct = self::where('id', $productId)
                          ->where('municipality', $fromMunicipality)
                          ->lockForUpdate()
                          ->firstOrFail();
        
        // Verificar stock disponible
        $available = $originProduct->purchase->quantity - $originProduct->sales()->sum('quantity');
        if ($quantity > $available) {
            throw new \Exception("Stock insuficiente en {$fromMunicipality}");
        }

        // 2. Buscar o crear producto en el municipio destino
        $destinationProduct = self::firstOrCreate(
            [
                'purchase_id' => $originProduct->purchase_id,
                'municipality' => $toMunicipality
            ],
            [
                'price' => $originProduct->price,
                'discount' => $originProduct->discount,
                'description' => $originProduct->description,
                'category_id' => $originProduct->category_id
            ]
        );

        // 3. No necesitamos actualizar cantidades físicas aquí porque 
        // el stock se calcula dinámicamente basado en purchases - sales
    });
}

public function inventoryAdjustments()
{
    return $this->hasMany(InventoryAdjustment::class);
}

// Método para calcular stock disponible incluyendo transferencias
public function getAvailableStockAttribute()
{
    $purchased = $this->purchase->quantity ?? 0;
    $sold = $this->sales()->sum('quantity');
    $transferredIn = $this->inventoryAdjustments()->where('type', 'transfer_in')->sum('quantity');
    
    return $purchased + $transferredIn - $sold;
}
}
