<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
    'batch_number', 'product', 'category_id', 'supplier_id', 
    'cost_price', 'quantity', 'expiry_date', 'image', 'notes',
    'serie', 'riesgo', 'vida_util', 'registro_sanitario', 
    'presentacion_comercial', 'forma_farmaceutica', 
    'concentracion', 'unidad_medida', 'marca'
];

    protected $casts = [
        'expiry_date' => 'date'
    ];

    // Generar número de lote automáticamente
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($purchase) {
            if (empty($purchase->batch_number)) {
                $purchase->batch_number = self::generateBatchNumber();
            }
        });
    }

    public static function generateBatchNumber()
    {
        $prefix = 'LOTE-' . date('Y');
        $lastBatch = self::where('batch_number', 'like', $prefix . '%')
                        ->latest('id')
                        ->first();
        
        if ($lastBatch) {
            $lastNumber = intval(substr($lastBatch->batch_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . '-' . $newNumber;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Verificar si el lote está próximo a vencer (30 días)
    public function isNearExpiry()
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 30;
    }

    // Verificar si el lote está vencido
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // Obtener stock disponible del lote
    public function getAvailableStockAttribute()
    {
        $sold = $this->products()->withTrashed()->get()->sum(function($product) {
            return $product->sales()->sum('quantity');
        });
        
        return $this->quantity - $sold;
    }

    // Scope para filtrar por estado de vencimiento
    public function scopeNearExpiry($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }
}