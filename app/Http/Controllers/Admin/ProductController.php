<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
public function index(Request $request)
{
    $title = 'products';
    $municipality = $request->get('municipality', 'all'); // Obtener filtro de municipio
    
    if ($request->ajax()) {
        $products = Product::with(['purchase.category', 'sales', 'inventoryAdjustments']);
        
        // Filtrar por municipio si se especifica
        if ($municipality !== 'all') {
            $products->where('municipality', $municipality);
        }
        
        $products = $products->latest();
        
        return DataTables::of($products)
            ->addColumn('batch_number', function($product) {
                return $product->purchase->batch_number ?? 'N/A';
            })
            ->addColumn('product', function($product) {
                if (!$product->purchase) return 'N/A';
                
                $image = $product->purchase->image ? 
                    '<span class="avatar avatar-sm mr-2">
                        <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'">
                    </span>' : '';
                
                return $product->purchase->product . ' ' . $image;
            })
            ->addColumn('marca', function($product) {
                return $product->purchase->marca ?? 'N/A';
            })
            ->addColumn('municipality', function($product) {
                $badges = [
                    'cajibio' => '<span class="badge badge-info">Cajibío</span>',
                    'morales' => '<span class="badge badge-success">Morales</span>',
                    'piendamo' => '<span class="badge badge-warning">Piendamó</span>'
                ];
                return $badges[$product->municipality] ?? $product->municipality;
            })
            ->addColumn('category', function($product) {
                return $product->purchase->category->name ?? 'N/A';
            })
            // Nuevos campos agregados
            ->addColumn('serie', function($product) {
                return $product->purchase->serie ?? 'N/A';
            })
            ->addColumn('riesgo', function($product) {
                return $product->purchase->riesgo ?? 'N/A';
            })
            ->addColumn('vida_util', function($product) {
                return $product->purchase->vida_util ?? 'N/A';
            })
            ->addColumn('registro_sanitario', function($product) {
                return $product->purchase->registro_sanitario ?? 'N/A';
            })
            ->addColumn('presentacion_comercial', function($product) {
                return $product->purchase->presentacion_comercial ?? 'N/A';
            })
            ->addColumn('forma_farmaceutica', function($product) {
                return $product->purchase->forma_farmaceutica ?? 'N/A';
            })
            ->addColumn('concentracion', function($product) {
                return $product->purchase->concentracion ?? 'N/A';
            })
            ->addColumn('unidad_medida', function($product) {
                return $product->purchase->unidad_medida ?? 'N/A';
            })
            ->addColumn('price', function($product) {
                return settings('app_currency', '$').' '.number_format($product->price, 2);
            })
            ->addColumn('quantity', function($product) {
                if (!$product->purchase) return 'N/A';
                
                // Obtener cantidad vendida
                $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
                
                // Obtener transferencias recibidas
                $transferred_in = \App\Models\InventoryAdjustment::where('product_id', $product->id)
                    ->where('type', 'transfer_in')
                    ->sum('quantity');
                
                // Verificar si este producto fue creado originalmente en este municipio
                // Esto lo hacemos verificando si existe otro producto con la misma purchase_id 
                // en un municipio diferente que tenga ventas o transferencias más antiguas
                $isOriginalInThisMunicipality = true;
                
                if ($transferred_in > 0) {
                    // Buscar si existe el mismo producto (misma purchase_id) en otros municipios
                    $otherMunicipalityProducts = \App\Models\Product::where('purchase_id', $product->purchase_id)
                        ->where('municipality', '!=', $product->municipality)
                        ->get();
                    
                    if ($otherMunicipalityProducts->count() > 0) {
                        // Verificar si alguno de los otros municipios tiene ventas más antiguas
                        $firstSaleThisMunicipality = \App\Models\Sale::where('product_id', $product->id)
                            ->orderBy('created_at', 'asc')
                            ->first();
                        
                        foreach ($otherMunicipalityProducts as $otherProduct) {
                            $firstSaleOtherMunicipality = \App\Models\Sale::where('product_id', $otherProduct->id)
                                ->orderBy('created_at', 'asc')
                                ->first();
                            
                            if ($firstSaleOtherMunicipality && 
                                (!$firstSaleThisMunicipality || 
                                 $firstSaleOtherMunicipality->created_at < $firstSaleThisMunicipality->created_at)) {
                                $isOriginalInThisMunicipality = false;
                                break;
                            }
                        }
                    }
                }
                
                if ($isOriginalInThisMunicipality) {
                    // Es producto original en este municipio: cantidad base + transferencias - vendidas
                    $base_quantity = $product->purchase->quantity;
                    $available = $base_quantity + $transferred_in - $already_sold;
                } else {
                    // Es producto transferido: solo transferencias - vendidas
                    $available = $transferred_in - $already_sold;
                }
                
                return '<span class="'.($available <= 0 ? 'text-danger' : ($available <= 1 ? 'text-warning' : 'text-success')).'">
                    '.$available.'
                </span>';
            })
            ->addColumn('discount', function($product) {
                return $product->discount ? $product->discount.'%' : '0%';
            })
            ->addColumn('expiry_date', function($product) {
                if (!$product->purchase || !$product->purchase->expiry_date) return 'N/A';
                return date('d M, Y', strtotime($product->purchase->expiry_date));
            })
            ->addColumn('action', function($product) {
                $editBtn = auth()->user()->can('edit-product') ? 
                    '<a href="'.route('products.edit', $product->id).'" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                    </a>' : '';
                
                $deleteBtn = auth()->user()->can('destroy-product') ?
                    '<button class="btn btn-sm btn-danger delete-btn" 
                            data-id="'.$product->id.'"
                            data-route="'.route('products.destroy', $product->id).'">
                        <i class="fas fa-trash"></i>
                    </button>' : '';
                
                return $editBtn.' '.$deleteBtn;
            })
            ->filterColumn('batch_number', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('batch_number', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('product', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('product', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('marca', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('marca', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('municipality', function($query, $keyword) {
                $query->where('municipality', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('category', function($query, $keyword) {
                $query->whereHas('purchase.category', function($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                });
            })
            // Filtros para los nuevos campos
            ->filterColumn('serie', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('serie', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('riesgo', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('riesgo', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('vida_util', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('vida_util', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('registro_sanitario', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('registro_sanitario', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('presentacion_comercial', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('presentacion_comercial', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('forma_farmaceutica', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('forma_farmaceutica', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('concentracion', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('concentracion', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('unidad_medida', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('unidad_medida', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('price', function($query, $keyword) {
                $query->where('price', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('discount', function($query, $keyword) {
                $query->where('discount', 'LIKE', "%{$keyword}%");
            })
            ->filterColumn('expiry_date', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('expiry_date', 'LIKE', "%{$keyword}%");
                });
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $searchValue = $request->search['value'];
                    $query->where(function($q) use ($searchValue) {
                        $q->whereHas('purchase', function($subQ) use ($searchValue) {
                            $subQ->where('product', 'LIKE', "%{$searchValue}%")
                            ->orWhere('marca', 'LIKE', "%{$searchValue}%")
                               ->orWhere('batch_number', 'LIKE', "%{$searchValue}%")
                                
                                ->orWhere('serie', 'LIKE', "%{$searchValue}%")
                                ->orWhere('riesgo', 'LIKE', "%{$searchValue}%")
                                ->orWhere('vida_util', 'LIKE', "%{$searchValue}%")
                                ->orWhere('registro_sanitario', 'LIKE', "%{$searchValue}%")
                                ->orWhere('presentacion_comercial', 'LIKE', "%{$searchValue}%")
                                ->orWhere('forma_farmaceutica', 'LIKE', "%{$searchValue}%")
                                ->orWhere('concentracion', 'LIKE', "%{$searchValue}%")
                                ->orWhere('unidad_medida', 'LIKE', "%{$searchValue}%")
                                ->orWhere('expiry_date', 'LIKE', "%{$searchValue}%");
                        })
                        ->orWhereHas('purchase.category', function($subQ) use ($searchValue) {
                            $subQ->where('name', 'LIKE', "%{$searchValue}%");
                        })
                        ->orWhere('municipality', 'LIKE', "%{$searchValue}%")
                        ->orWhere('price', 'LIKE', "%{$searchValue}%")
                        ->orWhere('discount', 'LIKE', "%{$searchValue}%");
                    });
                }
            })
            ->rawColumns(['product', 'municipality', 'quantity', 'riesgo', 'action'])
            ->make(true);
    }
    
    // Obtener estadísticas por municipio
    $stats = [
        'cajibio' => Product::where('municipality', 'cajibio')->count(),
        'morales' => Product::where('municipality', 'morales')->count(),
        'piendamo' => Product::where('municipality', 'piendamo')->count(),
        'total' => Product::count()
    ];
    
    return view('admin.products.index', compact('title', 'municipality', 'stats'));
}
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'add product';
        $purchases = Purchase::get();
        return view('admin.products.create',compact(
            'title','purchases'
        ));
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $this->validate($request,[
        'product'=>'required|max:200',
        'municipality'=>'required|in:cajibio,morales,piendamo',
        'price'=>'required|min:1',
        'discount'=>'nullable',
        'description'=>'nullable|max:255',
    ]);
    
    $price = $request->price;
    if($request->discount > 0){
       $price = $request->price - ($request->discount * $request->price / 100);
    }
    
    Product::create([
        'purchase_id'=>$request->product,
        'municipality'=>$request->municipality,
        'price'=>$price,
        'discount'=>$request->discount,
        'description'=>$request->description,
    ]);
    
    $notification = notify("Product has been added");
    return redirect()->route('products.index')->with($notification);
}

    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $title = 'edit product';
        $purchases = Purchase::get();
        return view('admin.products.edit',compact(
            'title','product','purchases'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request,[
            'product'=>'required|max:200',
            'price'=>'required',
            'discount'=>'nullable',
            'description'=>'nullable|max:255',
        ]);
        
        $price = $request->price;
        if($request->discount >0){
           $price = $request->discount * $request->price;
        }
       $product->update([
            'purchase_id'=>$request->product,
            'price'=>$price,
            'discount'=>$request->discount,
            'description'=>$request->description,
        ]);
        $notification = notify('product has been updated');
        return redirect()->route('products.index')->with($notification);
    }

     /**
     * Display a listing of expired resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
public function expired(Request $request)
{
    $title = "expired Products";
    $municipality = $request->get('municipality', 'all');
    
    if($request->ajax()){
        try {
            // Consulta modificada para solo productos vencidos (expiry_date < hoy)
            $purchases = Purchase::whereNotNull('expiry_date')
                ->where('expiry_date', '<', now()) // Solo fechas pasadas
                ->with(['category', 'supplier'])
                ->whereHas('products', function($query) use ($municipality) {
                    if ($municipality !== 'all') {
                        $query->where('municipality', $municipality);
                    }
                })
                ->get();
            
            return DataTables::of($purchases)

            ->addColumn('batch_number', function($purchase) {
                return $purchase->batch_number ?? 'N/A';
            })
                ->addColumn('product', function($purchase) {
                    $image = '';
                    if(!empty($purchase->image)) {
                        $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$purchase->image).'" alt="image">
                            </span>';
                    }
                    return ($purchase->product ?? 'Sin nombre') . ' ' . $image;
                })
                ->addColumn('marca', function($purchase) {
                    return $purchase->marca ?? 'N/A';
                })
                ->addColumn('municipality', function($purchase) {
                    $product = \App\Models\Product::where('purchase_id', $purchase->id)->first();
                    if (!$product) return 'N/A';
                    
                    $badges = [
                        'cajibio' => '<span class="badge badge-info">Cajibío</span>',
                        'morales' => '<span class="badge badge-success">Morales</span>',
                        'piendamo' => '<span class="badge badge-warning">Piendamó</span>'
                    ];
                    return $badges[$product->municipality] ?? $product->municipality;
                })
                ->addColumn('category', function($purchase) {
                    return $purchase->category ? $purchase->category->name : 'Sin categoría';
                })
                ->addColumn('price', function($purchase) {
                    // Buscar el precio en la tabla products
                    $product = \App\Models\Product::where('purchase_id', $purchase->id)->first();
                    $price = $product ? $product->price : '0.00';
                    return settings('app_currency','$') . ' ' . $price;
                })
                ->addColumn('quantity', function($purchase) {
                    // CÁLCULO DINÁMICO DE STOCK DISPONIBLE
                    $product = \App\Models\Product::where('purchase_id', $purchase->id)->first();
                    if (!$product) {
                        return '<span class="text-muted">0</span>';
                    }
                    
                    $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
                    $available = $purchase->quantity - $already_sold;
                    
                    return '<span class="'.($available <= 0 ? 'text-danger' : ($available <= 1 ? 'text-warning' : 'text-success')).'">
                        '.$available.'
                    </span>';
                })
                ->addColumn('discount', function($purchase) {
                    // Buscar el descuento en la tabla products
                    $product = \App\Models\Product::where('purchase_id', $purchase->id)->first();
                    return $product ? ($product->discount ?? '0.00') : '0.00';
                })
                ->addColumn('expiry_date', function($purchase) {
                    if(!empty($purchase->expiry_date)) {
                        try {
                            return date('d M, Y', strtotime($purchase->expiry_date));
                        } catch(\Exception $e) {
                            return $purchase->expiry_date;
                        }
                    }
                    return 'Sin fecha';
                })
                // NUEVOS CAMPOS AGREGADOS
                ->addColumn('serie', function($purchase) {
                    return $purchase->serie ?? 'N/A';
                })
                ->addColumn('riesgo', function($purchase) {
                    return $purchase->riesgo ?? 'N/A';
                })
                ->addColumn('vida_util', function($purchase) {
                    return $purchase->vida_util ?? 'N/A';
                })
                ->addColumn('registro_sanitario', function($purchase) {
                    return $purchase->registro_sanitario ?? 'N/A';
                })
                ->addColumn('presentacion_comercial', function($purchase) {
                    return $purchase->presentacion_comercial ?? 'N/A';
                })
                ->addColumn('forma_farmaceutica', function($purchase) {
                    return $purchase->forma_farmaceutica ?? 'N/A';
                })
                ->addColumn('concentracion', function($purchase) {
                    return $purchase->concentracion ?? 'N/A';
                })
                ->addColumn('unidad_medida', function($purchase) {
                    return $purchase->unidad_medida ?? 'N/A';
                })
                ->rawColumns(['product', 'municipality', 'quantity'])
                ->make(true);
                
        } catch(\Exception $e) {
            \Log::error('Error in expired method: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al cargar los datos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Estadísticas por municipio para productos vencidos
    $expiredStats = [];
    
    $municipalities = ['cajibio', 'morales', 'piendamo'];
    
    foreach ($municipalities as $mun) {
        $expiredStats[$mun] = Purchase::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->whereHas('products', function($query) use ($mun) {
                $query->where('municipality', $mun);
            })
            ->count();
    }
    
    // Total de productos vencidos
    $expiredStats['total'] = Purchase::whereNotNull('expiry_date')
        ->where('expiry_date', '<', now())
        ->whereHas('products')
        ->count();
    
    return view('admin.products.expired', compact('title', 'municipality', 'expiredStats'));
}

/**
 * Display a listing of out of stock resources.
 *
 * @param  \Illuminate\Http\Request $request
 * @return \Illuminate\Http\Response
 */
public function outstock(Request $request) {     
    $title = "outstocked Products";
    $municipality = $request->get('municipality', 'all');
    
    if($request->ajax()){
        try {
            // Obtener TODOS los productos con sus compras y ventas
            $products = Product::with(['purchase.category', 'sales'])
                ->whereHas('purchase'); // Solo productos que tienen compra
            
            // Filtrar por municipio si se especifica
            if ($municipality !== 'all') {
                $products->where('municipality', $municipality);
            }
            
            // Obtener todos los productos
            $allProducts = $products->get();
            
            // Filtrar manualmente los productos agotados
            $outstockProducts = $allProducts->filter(function($product) {
                if (!$product->purchase) return false;
                
                // Calcular stock disponible
                $totalSold = $product->sales->sum('quantity');
                $available = $product->purchase->quantity - $totalSold;
                
                // Solo productos con stock <= 0
                return $available <= 0;
            });
            
            return DataTables::of($outstockProducts)
                ->addColumn('batch_number', function($product) {
                    return $product->purchase->batch_number ?? 'N/A';
                })
                ->addColumn('product', function($product) {
                    if (!$product->purchase) return 'N/A';
                    
                    $image = $product->purchase->image ? 
                        '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'">
                        </span>' : '';
                    
                    return $product->purchase->product . ' ' . $image;
                })
                ->addColumn('marca', function($product) {
                    return $product->purchase->marca ?? 'N/A';
                })
                ->addColumn('municipality', function($product) {
                    $badges = [
                        'cajibio' => '<span class="badge badge-info">Cajibío</span>',
                        'morales' => '<span class="badge badge-success">Morales</span>',
                        'piendamo' => '<span class="badge badge-warning">Piendamó</span>'
                    ];
                    return $badges[$product->municipality] ?? $product->municipality;
                })
                ->addColumn('category', function($product) {
                    return $product->purchase->category->name ?? 'N/A';
                })
                ->addColumn('price', function($product) {
                    return settings('app_currency','$') . ' ' . number_format($product->price, 2);
                })
                ->addColumn('quantity', function($product) {
                    if (!$product->purchase) return 'N/A';
                    
                    // Cálculo del stock disponible usando la relación ya cargada
                    $totalSold = $product->sales->sum('quantity');
                    $available = $product->purchase->quantity - $totalSold;
                    
                    return '<span class="text-danger">
                        '.$available.'
                    </span>';
                })
                ->addColumn('discount', function($product) {
                    return $product->discount ? $product->discount.'%' : '0%';
                })
                ->addColumn('expiry_date', function($product) {
                    if (!$product->purchase || !$product->purchase->expiry_date) return 'N/A';
                    return date('d M, Y', strtotime($product->purchase->expiry_date));
                })
                // NUEVOS CAMPOS AGREGADOS
                ->addColumn('serie', function($product) {
                    return $product->purchase->serie ?? 'N/A';
                })
                ->addColumn('riesgo', function($product) {
                    return $product->purchase->riesgo ?? 'N/A';
                })
                ->addColumn('vida_util', function($product) {
                    return $product->purchase->vida_util ?? 'N/A';
                })
                ->addColumn('registro_sanitario', function($product) {
                    return $product->purchase->registro_sanitario ?? 'N/A';
                })
                ->addColumn('presentacion_comercial', function($product) {
                    return $product->purchase->presentacion_comercial ?? 'N/A';
                })
                ->addColumn('forma_farmaceutica', function($product) {
                    return $product->purchase->forma_farmaceutica ?? 'N/A';
                })
                ->addColumn('concentracion', function($product) {
                    return $product->purchase->concentracion ?? 'N/A';
                })
                ->addColumn('unidad_medida', function($product) {
                    return $product->purchase->unidad_medida ?? 'N/A';
                })
                ->filter(function ($query) use ($request) {
                    
                })
                ->rawColumns(['product', 'municipality', 'quantity'])
                ->make(true);
                
        } catch (\Exception $e) {
            \Log::error('Error in outstock: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // Estadísticas actualizadas para mostrar solo productos agotados
    $outstockStats = [];
    
    // Calcular estadísticas por municipio solo para productos agotados
    $municipalities = ['cajibio', 'morales', 'piendamo'];
    
    foreach ($municipalities as $mun) {
        $products = Product::with(['purchase', 'sales'])
            ->where('municipality', $mun)
            ->whereHas('purchase')
            ->get();
            
        $count = $products->filter(function($product) {
            $totalSold = $product->sales->sum('quantity');
            $available = $product->purchase->quantity - $totalSold;
            return $available <= 0;
        })->count();
        
        $outstockStats[$mun] = $count;
    }
    
    // Total de productos agotados
    $allProducts = Product::with(['purchase', 'sales'])
        ->whereHas('purchase')
        ->get();
        
    $outstockStats['total'] = $allProducts->filter(function($product) {
        $totalSold = $product->sales->sum('quantity');
        $available = $product->purchase->quantity - $totalSold;
        return $available <= 0;
    })->count();
    
    return view('admin.products.outstock', compact('title', 'municipality', 'outstockStats'));
}
    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
  public function destroy(Product $product)
{
    try {
        // Verificar si hay ventas asociadas
        $hasSales = \App\Models\Sale::where('product_id', $product->id)->exists();
        
        if ($hasSales) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el producto porque tiene ventas asociadas'
            ], 422);
        }
        
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el producto: ' . $e->getMessage()
        ], 500);
    }
}
}
