<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Str;
use App\Models\Sale;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
public function index(Request $request)
{
    $title = 'sales';
    if($request->ajax()){
        // Crear una subconsulta para obtener los datos agrupados
        $salesSubquery = Sale::with(['product.purchase', 'customer'])
            ->whereNotNull('sale_group_id')
            ->select('sale_group_id', 'customer_id', 'created_at')
            ->selectRaw('COUNT(*) as product_count')
            ->selectRaw('SUM(total_price) as total_amount')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('MIN(id) as first_sale_id')
            ->groupBy('sale_group_id', 'customer_id', 'created_at');

        // Crear la consulta principal con JOINs para permitir búsqueda
        $salesGrouped = DB::table(DB::raw("({$salesSubquery->toSql()}) as grouped_sales"))
            ->mergeBindings($salesSubquery->getQuery())
            ->leftJoin('customers', 'grouped_sales.customer_id', '=', 'customers.id')
            ->select(
                'grouped_sales.*',
                'customers.name as customer_name',
                'customers.email as customer_email',
                'customers.phone as customer_phone'
            )
            ->orderBy('grouped_sales.created_at', 'desc');

        return DataTables::of($salesGrouped)
            ->addIndexColumn()
            ->addColumn('products', function($saleGroup){
                // Obtener todos los productos de este grupo específico
                $sales = Sale::with(['product.purchase'])
                    ->where('sale_group_id', $saleGroup->sale_group_id)
                    ->where('customer_id', $saleGroup->customer_id)
                    ->get();
                
                if ($sales->isEmpty()) {
                    return 'Sin productos';
                }
                
                // Agrupar productos iguales y sumar sus cantidades
                $groupedProducts = [];
                $productNames = []; // Para búsqueda
                
                foreach($sales as $sale) {
                    if($sale->product && $sale->product->purchase) {
                        $productName = $sale->product->purchase->product ?? 'Producto sin nombre';
                        $productNames[] = $productName; // Guardar nombres para búsqueda
                        
                        if(!isset($groupedProducts[$productName])) {
                            $groupedProducts[$productName] = [
                                'quantity' => 0,
                                'image' => $sale->product->purchase->image ?? null,
                                'product_id' => $sale->product->id
                            ];
                        }
                        $groupedProducts[$productName]['quantity'] += $sale->quantity;
                    }
                }
                
                // Construir la lista de productos agrupados
                $productList = '';
                foreach($groupedProducts as $name => $data) {
                    // Calcular cantidad disponible actual del producto
                    $product = Product::find($data['product_id']);
                    $availableQuantity = 0;
                    
                    if ($product && $product->purchase) {
                        // Obtener cantidad vendida (excluyendo esta venta para mostrar disponibilidad actual)
                        $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
                        
                        // Obtener transferencias recibidas
                        $transferred_in = \App\Models\InventoryAdjustment::where('product_id', $product->id)
                            ->where('type', 'transfer_in')
                            ->sum('quantity');
                        
                        // Verificar si este producto fue creado originalmente en este municipio
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
                            $availableQuantity = $base_quantity + $transferred_in - $already_sold;
                        } else {
                            // Es producto transferido: solo transferencias - vendidas
                            $availableQuantity = $transferred_in - $already_sold;
                        }
                    }
                    
                    $image = '';
                    if(!empty($data['image'])) {
                        $image = '<span class="avatar avatar-sm mr-1">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$data['image']).'" alt="image">
                            </span>';
                    }
                    
                    // Determinar color de disponibilidad
                    $availabilityClass = $availableQuantity <= 0 ? 'text-danger' : ($availableQuantity <= 1 ? 'text-warning' : 'text-success');
                    
                    $productList .= '<div class="product-item mb-2 p-2 border rounded">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                ' . $image . '
                                <div>
                                    <div><strong>' . $name . '</strong></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div><span class="badge badge-primary">Vendido: ' . $data['quantity'] . '</span></div>
                                <div class="small '.$availabilityClass.'">Disponible: ' . $availableQuantity . '</div>
                            </div>
                        </div>
                    </div>';
                }
                
                // Agregar los nombres de productos como atributo oculto para búsqueda
                $productList .= '<span style="display:none;">'.implode(' ', $productNames).'</span>';
                
                return $productList ?: 'Sin productos';
            })
          ->addColumn('municipality', function($saleGroup){
    // Obtener todos los productos de este grupo específico
    $sales = Sale::with(['product.purchase'])
        ->where('sale_group_id', $saleGroup->sale_group_id)
        ->where('customer_id', $saleGroup->customer_id)
        ->get();
    
    if ($sales->isEmpty()) {
        return 'N/A';
    }
    
    // Mapeo de nombres de municipios
    $municipalityNames = [
        'cajibio' => 'Cajibío',
        'morales' => 'Morales',
        'piendamo' => 'Piendamó'
    ];
    
    $municipalityInfo = [];
    
    foreach($sales as $sale) {
        if($sale->product && $sale->product->purchase) {
            // PRIMERO: Verificar los campos directos de la venta
            $originMunicipality = $sale->origin_municipality ?? null;
            $destinationMunicipality = $sale->destination_municipality ?? null;
            $saleType = $sale->sale_type ?? 'sale';
            
            // Caso 1: Venta entre municipios diferentes (origin ≠ destination)
            if ($saleType === 'sale' && $originMunicipality && $destinationMunicipality && $originMunicipality !== $destinationMunicipality) {
                $originName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                $destinationName = $municipalityNames[$destinationMunicipality] ?? ucfirst($destinationMunicipality);
                $municipalityInfo[] = "{$originName} vendió a {$destinationName}";
            }
            // Caso 2: Venta dentro del mismo municipio (origin = destination)
            else if ($saleType === 'sale' && $originMunicipality && $destinationMunicipality && $originMunicipality === $destinationMunicipality) {
                $municipalityName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                $municipalityInfo[] = $municipalityName;
            }
            // Caso 3: Solo tenemos origin_municipality (venta local)
            else if ($saleType === 'sale' && $originMunicipality && !$destinationMunicipality) {
                $municipalityName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                $municipalityInfo[] = $municipalityName;
            }
            // Caso 4: Transferencia (sale_type = 'transfer' o similar)
            else if ($saleType === 'transfer' && $originMunicipality && $destinationMunicipality) {
                $originName = $municipalityNames[$originMunicipality] ?? ucfirst($originMunicipality);
                $destinationName = $municipalityNames[$destinationMunicipality] ?? ucfirst($destinationMunicipality);
                $municipalityInfo[] = "De {$originName} transferido a {$destinationName}";
            }
            // Caso 5: Fallback - usar lógica antigua solo si no hay información directa
            else {
                $productMunicipality = $sale->product->municipality;
                
                // Verificar si es un producto transferido (lógica existente - solo como fallback)
                $transferred_in = \App\Models\InventoryAdjustment::where('product_id', $sale->product->id)
                    ->where('type', 'transfer_in')
                    ->sum('quantity');
                
                if ($transferred_in > 0) {
                    $originalProduct = \App\Models\Product::where('purchase_id', $sale->product->purchase_id)
                        ->where('municipality', '!=', $productMunicipality)
                        ->first();
                    
                    if ($originalProduct) {
                        $fromName = $municipalityNames[$originalProduct->municipality] ?? ucfirst($originalProduct->municipality);
                        $toName = $municipalityNames[$productMunicipality] ?? ucfirst($productMunicipality);
                        $municipalityInfo[] = "De {$fromName} transferido a {$toName}";
                    } else {
                        $municipalityInfo[] = $municipalityNames[$productMunicipality] ?? ucfirst($productMunicipality);
                    }
                } else {
                    $municipalityInfo[] = $municipalityNames[$productMunicipality] ?? ucfirst($productMunicipality);
                }
            }
        }
    }
    
    // Eliminar duplicados y retornar
    $uniqueInfo = array_unique($municipalityInfo);
    return implode('<br>', $uniqueInfo);
})
            ->addColumn('customer', function($saleGroup){
                return $saleGroup->customer_name ?? 'Cliente no encontrado';
            })
            ->addColumn('total_price', function($saleGroup){                   
                return settings('app_currency','$').' '. number_format($saleGroup->total_amount ?? 0, 2);
            })
            ->addColumn('total_quantity', function($saleGroup){                   
                return ($saleGroup->total_quantity ?? 0) . ' items';
            })
            ->addColumn('date', function($saleGroup){
                return $saleGroup->created_at ? date_format(date_create($saleGroup->created_at),'d M, Y') : 'N/A';
            })
            ->addColumn('action', function ($saleGroup) {
                // Usar el primer ID de venta del grupo para las acciones
                $firstSale = Sale::where('sale_group_id', $saleGroup->sale_group_id)
                    ->where('customer_id', $saleGroup->customer_id)
                    ->first();
                
                if (!$firstSale) {
                    return 'N/A';
                }
                
             
                
                // Verificar que sale_group_id no sea null y manejar valores por defecto
                $saleGroupIdValue = $saleGroup->sale_group_id ?? '';
                $customerIdValue = $saleGroup->customer_id ?? '';
                
                $deletebtn = '<button id="deletebtn" 
                                data-group-id="'.$saleGroupIdValue.'" 
                                data-customer-id="'.$customerIdValue.'" 
                                data-route="'.route('sales.destroy-group').'" 
                                class="btn btn-danger btn-sm" 
                                title="Eliminar grupo de ventas">
                                <i class="fas fa-trash"></i>
                              </button>';
                
               $invoicebtn = '<a href="'.route('sales.invoice-group', $saleGroupIdValue).'" target="_blank" class="btn btn-success btn-sm" title="Generar factura"><i class="fas fa-file-invoice"></i></a>';
                
                
                if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                    $deletebtn = '';
                }
                
                $btn = $invoicebtn.'  '.$deletebtn;
                return $btn;
            })
            // Configurar filtros personalizados para mejorar la búsqueda
            ->filterColumn('customer', function($query, $keyword) {
                $query->where('customers.name', 'like', "%{$keyword}%")
                      ->orWhere('customers.email', 'like', "%{$keyword}%")
                      ->orWhere('customers.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('products', function($query, $keyword) {
                // Filtrar por productos usando una subconsulta
                $query->whereExists(function($subQuery) use ($keyword) {
                    $subQuery->select(DB::raw(1))
                             ->from('sales')
                             ->join('products', 'sales.product_id', '=', 'products.id')
                             ->join('purchases', 'products.purchase_id', '=', 'purchases.id')
                             ->whereColumn('sales.sale_group_id', 'grouped_sales.sale_group_id')
                             ->where(function($q) use ($keyword) {
                                 $q->where('purchases.product', 'like', "%{$keyword}%")
                                   ->orWhere('products.municipality', 'like', "%{$keyword}%");
                             });
                });
            })
            ->filterColumn('municipality', function($query, $keyword) {
                // Filtrar por municipio
                $query->whereExists(function($subQuery) use ($keyword) {
                    $subQuery->select(DB::raw(1))
                             ->from('sales')
                             ->join('products', 'sales.product_id', '=', 'products.id')
                             ->whereColumn('sales.sale_group_id', 'grouped_sales.sale_group_id')
                             ->where('products.municipality', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('total_price', function($query, $keyword) {
                // Permitir búsqueda por precio
                $numericKeyword = preg_replace('/[^\d.]/', '', $keyword);
                if(!empty($numericKeyword)) {
                    $query->where('grouped_sales.total_amount', 'like', "%{$numericKeyword}%");
                }
            })
            ->filterColumn('date', function($query, $keyword) {
                // Permitir búsqueda por fecha
                $query->where('grouped_sales.created_at', 'like', "%{$keyword}%");
            })
            ->rawColumns(['products','municipality','action'])
            ->make(true);
    }
    
    $products = Product::get();
    return view('admin.sales.index',compact('title','products'));
}
   
   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
public function create()
{
    $title = 'create sales';
    $municipalities = ['cajibio' => 'Cajibío', 'morales' => 'Morales', 'piendamo' => 'Piendamó'];
    
    $productsByMunicipality = Product::with(['purchase'])
        ->get()
        ->groupBy('municipality')
        ->map(function ($products) {
            return $products->filter(function ($product) {
                if (!$product->purchase) return false;
                
                $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
                $transferred_in = \App\Models\InventoryAdjustment::where('product_id', $product->id)
                    ->where('type', 'transfer_in')->sum('quantity');
                
                // LÓGICA SÚPER SIMPLE Y CLARA:
                // Si tiene transferencias recibidas = SIEMPRE es producto transferido
                // Si NO tiene transferencias = es producto original
                
                if ($transferred_in > 0) {
                    // PRODUCTO TRANSFERIDO - SOLO cuenta las transferencias
                    $available = $transferred_in - $already_sold;
                    $product->is_original = false;
                } else {
                    // PRODUCTO ORIGINAL - cuenta la compra base
                    $available = $product->purchase->quantity - $already_sold;
                    $product->is_original = true;
                }
                
                $product->available = max($available, 0);
                $product->transferred_in = $transferred_in;
                $product->base_quantity = $product->purchase->quantity;
                
                return $product->available > 0;
            })->values();
        });
    
    $customers = Customer::all();
    
    return view('admin.sales.create', compact(
        'title',
        'productsByMunicipality',
        'customers',
        'municipalities'
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
    $this->validate($request, [
        'customer_id' => 'required|exists:customers,id',
        'sales_type' => 'required|in:local,transfer',
        'origin_municipality' => 'required_if:sales_type,local|in:cajibio,morales,piendamo',
        'destination_municipality' => 'required_if:sales_type,transfer|in:cajibio,morales,piendamo',
        'products' => 'required|array|min:1',
        'products.*.product_id' => 'required|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1',
        'total_price' => 'required|numeric|min:0'
    ]);

    $customer_id = $request->customer_id;
    $products = $request->products;
    $errors = [];
    $successful_sales = [];

    // Determinar municipios
    $originMunicipality = $request->origin_municipality ?? 'cajibio';
    $destinationMunicipality = ($request->sales_type == 'local') 
        ? $originMunicipality 
        : ($request->destination_municipality ?? 'cajibio');

    // Validar municipios diferentes para transferencias
    if ($request->sales_type == 'transfer' && $originMunicipality == $destinationMunicipality) {
        $errors[] = "Para transferencias, el municipio origen y destino deben ser diferentes";
    }

    // Generar ID de grupo
    $sale_group_id = \Illuminate\Support\Str::uuid()->toString();

    // Verificar productos
    foreach ($products as $index => $productData) {
        $product = Product::with('purchase')->find($productData['product_id']);
        
        if (!$product || !$product->purchase) {
            $errors[] = "Producto no encontrado o sin compra asociada.";
            continue;
        }

        // Validar municipio origen
        if ($product->municipality != $originMunicipality) {
            $errors[] = "El producto '{$product->purchase->product}' no pertenece a {$originMunicipality}";
            continue;
        }

        // ✅ VALIDAR STOCK CORREGIDO - Aplicar la misma lógica que en Products e Index
        $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
        
        // Obtener transferencias recibidas
        $transferred_in = \App\Models\InventoryAdjustment::where('product_id', $product->id)
            ->where('type', 'transfer_in')
            ->sum('quantity');
        
        // Verificar si este producto fue creado originalmente en este municipio
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

        if ($productData['quantity'] > $available) {
            $errors[] = "Stock insuficiente para {$product->purchase->product} (Disponible: {$available})";
        }
    }
    
    if (!empty($errors)) {
        return redirect()->back()
            ->withErrors($errors)
            ->withInput();
    }
    
    // Procesar la venta/transferencia
    \DB::transaction(function() use ($products, $customer_id, $sale_group_id, $originMunicipality, $destinationMunicipality, $request, &$successful_sales) {
        foreach ($products as $index => $productData) {
            $product = Product::with('purchase')->find($productData['product_id']);
            
            // Para transferencias entre municipios
            if ($request->sales_type == 'transfer' && $originMunicipality != $destinationMunicipality) {
                // Verificar si ya existe el producto en el municipio destino
                $destProduct = Product::where('purchase_id', $product->purchase_id)
                                      ->where('municipality', $destinationMunicipality)
                                      ->first();
                
                if (!$destProduct) {
                    // Si no existe, crear el producto en destino
                    $destProduct = Product::create([
                        'purchase_id' => $product->purchase_id,
                        'municipality' => $destinationMunicipality,
                        'price' => $product->price,
                        'discount' => $product->discount,
                        'description' => $product->description,
                        'category_id' => $product->category_id
                    ]);
                }
                
                // ✅ CREAR EL AJUSTE DE INVENTARIO PARA LA CANTIDAD TRANSFERIDA
                InventoryAdjustment::create([
                    'product_id' => $destProduct->id,
                    'type' => 'transfer_in',
                    'quantity' => $productData['quantity'],
                    'reference' => "Transferencia desde {$originMunicipality}",
                    'sale_group_id' => $sale_group_id
                ]);
            }
            
            // Registrar la venta
            $sale = Sale::create([
                'product_id' => $productData['product_id'],
                'customer_id' => $customer_id,
                'quantity' => $productData['quantity'],
                'total_price' => $productData['quantity'] * $product->price,
                'sale_group_id' => $sale_group_id,
                'origin_municipality' => $originMunicipality,
                'destination_municipality' => $destinationMunicipality,
                'sale_type' => $request->sales_type
            ]);
            
            $successful_sales[] = $sale;
        }
    });
    
    // Mensaje de éxito
    $message = ($request->sales_type == 'local')
        ? "Venta en {$originMunicipality} registrada"
        : "Transferencia de {$originMunicipality} a {$destinationMunicipality} completada";
    
    $notification = notify("$message con ".count($successful_sales)." producto(s)");
    
    return redirect()->route('sales.index')->with($notification);
}
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    // Método edit actualizado
public function edit(Sale $sale)
{
    $title = 'edit sale';
    $municipalities = ['cajibio' => 'Cajibío', 'morales' => 'Morales', 'piendamo' => 'Piendamó'];
    $customers = Customer::all();
   
    $groupSales = Sale::with(['product.purchase', 'customer'])
        ->where('sale_group_id', $sale->sale_group_id)
        ->where('customer_id', $sale->customer_id)
        ->orderBy('created_at', 'asc')
        ->get();

    // Obtener productos agrupados por municipio con la misma lógica del create
    $productsByMunicipality = Product::with(['purchase'])
        ->get()
        ->groupBy('municipality')
        ->map(function ($products) {
            return $products->filter(function ($product) {
                if (!$product->purchase) return false;
                                
                $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
                $transferred_in = \App\Models\InventoryAdjustment::where('product_id', $product->id)
                    ->where('type', 'transfer_in')->sum('quantity');
                                
                // LÓGICA SÚPER SIMPLE Y CLARA:
                // Si tiene transferencias recibidas = SIEMPRE es producto transferido
                // Si NO tiene transferencias = es producto original
                                
                if ($transferred_in > 0) {
                    // PRODUCTO TRANSFERIDO - SOLO cuenta las transferencias
                    $available = $transferred_in - $already_sold;
                    $product->is_original = false;
                } else {
                    // PRODUCTO ORIGINAL - cuenta la compra base
                    $available = $product->purchase->quantity - $already_sold;
                    $product->is_original = true;
                }
                                
                $product->available = max($available, 0);
                $product->transferred_in = $transferred_in;
                $product->base_quantity = $product->purchase->quantity;
                $product->batch_number = $product->purchase->batch_number ?? 'N/A';
                                
                return $product->available > 0;
            })->values();
        });
    
    return view('admin.sales.edit', compact(
        'title', 'sale', 'productsByMunicipality', 'customers', 'groupSales', 'municipalities'
    ));
}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request, Sale $sale)
{
    $this->validate($request,[
        'product' => 'required',
        'quantity' => 'required|integer|min:1',
        'customer_id' => 'required|exists:customers,id',
        'sale_group_id' => 'required',
        'origin_municipality' => 'required',
        'destination_municipality' => 'required',
    ]);
    
    $sold_product = Product::find($request->product);
    
    if (!$sold_product || !$sold_product->purchase) {
        return redirect()->back()->with('error', 'Product not found or has no purchase record.');
    }
    
    $purchased_item = Purchase::find($sold_product->purchase->id);
    
    // Detectar tipo de venta
    $sale_type = 'sale'; // por defecto
    if ($request->origin_municipality !== $request->destination_municipality) {
        $sale_type = 'sale'; // Venta entre municipios
    }
    
    // Calcular stock disponible usando la misma lógica del create/index
    $already_sold = Sale::where('product_id', $sold_product->id)->sum('quantity');
    $transferred_in = \App\Models\InventoryAdjustment::where('product_id', $sold_product->id)
        ->where('type', 'transfer_in')->sum('quantity');
    
    // Si es el mismo producto, excluir esta venta actual del cálculo
    if ($sale->product_id == $request->product) {
        $already_sold = Sale::where('product_id', $sold_product->id)
                           ->where('id', '!=', $sale->id)
                           ->sum('quantity');
    }
    
    // Calcular disponibilidad usando la misma lógica
    if ($transferred_in > 0) {
        // PRODUCTO TRANSFERIDO - SOLO cuenta las transferencias
        $available_stock = $transferred_in - $already_sold;
    } else {
        // PRODUCTO ORIGINAL - cuenta la compra base
        $available_stock = $purchased_item->quantity - $already_sold;
    }
    
    if ($request->quantity > $available_stock) {
        return redirect()->back()->with('error', 'Insufficient stock. Available quantity: ' . $available_stock);
    }

    // Calcular precio total del item
    $total_price = ($request->quantity) * ($sold_product->price);
    
    // Actualizar venta
    $sale->update([
        'product_id' => $request->product,
        'customer_id' => $request->customer_id,
        'quantity' => $request->quantity,
        'total_price' => $total_price,
        'sale_group_id' => $request->sale_group_id,
        'origin_municipality' => $request->origin_municipality,
        'destination_municipality' => $request->destination_municipality,
        'sale_type' => $sale_type,
    ]);

    $notification = notify("Sale has been updated successfully");
    
    // Verificar stock bajo después de la actualización
    $already_sold_after = Sale::where('product_id', $sold_product->id)->sum('quantity');
    $transferred_in_after = \App\Models\InventoryAdjustment::where('product_id', $sold_product->id)
        ->where('type', 'transfer_in')->sum('quantity');
    
    if ($transferred_in_after > 0) {
        $remaining = $transferred_in_after - $already_sold_after;
    } else {
        $remaining = $purchased_item->quantity - $already_sold_after;
    }
    
    if($remaining <= 1 && $remaining > 0){
        event(new PurchaseOutStock($purchased_item));
        $notification = notify("Product is running out of stock!");
    }
    
    return redirect()->route('sales.index')->with($notification);
}

    /**
     * Generate sales reports index
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request){
        $title = 'sales reports';
        return view('admin.sales.reports',compact(
            'title'
        ));
    }

    /**
     * Generate sales report form post
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
        $title = 'sales reports';
        $sales = Sale::with(['product.purchase', 'customer'])
                    ->whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))
                    ->get();
        return view('admin.sales.reports',compact(
            'sales','title'
        ));
    }

 
public function invoiceGroup($sale_group_id)
{
    $sales = Sale::with(['product.purchase', 'customer'])
               ->where('sale_group_id', $sale_group_id)
               ->get();

    if ($sales->isEmpty()) {
        abort(404, 'Grupo de ventas no encontrado');
    }

    $data = [
        'sales' => $sales,
        'customer' => $sales->first()->customer,
        'created_at' => $sales->first()->created_at,
        'total_price' => $sales->sum('total_price'),
        'sale_group_id' => $sale_group_id
    ];

    // Siempre devuelve PDF
    $pdf = Pdf::loadView('admin.sales.invoice-group', $data);
    return $pdf->download('factura-'.$sale_group_id.'.pdf');
}

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
// MÉTODO 1: Eliminar venta individual (AJAX)
public function destroy(Request $request)  
{     
    DB::beginTransaction();     
    try {         
        $sale = Sale::with(['product.purchase'])->findOrFail($request->id);                  
        
        // Guardar datos importantes antes de eliminar         
        $productId = $sale->product_id;         
        $quantitySold = $sale->quantity;         
        $saleGroupId = $sale->sale_group_id;
        
        // Verificar si es una venta entre municipios diferentes
        if ($sale->sale_type === 'sale' && 
            $sale->origin_municipality && 
            $sale->destination_municipality && 
            $sale->origin_municipality !== $sale->destination_municipality) {
            
            \Log::info('Procesando eliminación de venta inter-municipal', [
                'sale_id' => $sale->id,
                'origin' => $sale->origin_municipality,
                'destination' => $sale->destination_municipality,
                'quantity' => $sale->quantity
            ]);

            // Buscar el producto en el municipio de destino
            $destinationProduct = \App\Models\Product::where('purchase_id', $sale->product->purchase_id)
                ->where('municipality', $sale->destination_municipality)
                ->first();

            if ($destinationProduct) {
                // Eliminar los ajustes de inventario relacionados con esta venta específica
                $transferOutAdjustment = \App\Models\InventoryAdjustment::where('product_id', $sale->product_id)
                    ->where('type', 'transfer_out')
                    ->where('quantity', $sale->quantity)
                    ->where('created_at', '>=', $sale->created_at->subMinutes(5))
                    ->where('created_at', '<=', $sale->created_at->addMinutes(5))
                    ->first();

                $transferInAdjustment = \App\Models\InventoryAdjustment::where('product_id', $destinationProduct->id)
                    ->where('type', 'transfer_in')
                    ->where('quantity', $sale->quantity)
                    ->where('created_at', '>=', $sale->created_at->subMinutes(5))
                    ->where('created_at', '<=', $sale->created_at->addMinutes(5))
                    ->first();

                // Eliminar los ajustes de inventario
                if ($transferOutAdjustment) {
                    $transferOutAdjustment->delete();
                    \Log::info('Eliminado transfer_out individual', ['adjustment_id' => $transferOutAdjustment->id]);
                }

                if ($transferInAdjustment) {
                    $transferInAdjustment->delete();
                    \Log::info('Eliminado transfer_in individual', ['adjustment_id' => $transferInAdjustment->id]);
                }

                // Limpiar caché del producto de destino también
                \Cache::forget("product_stock_{$destinationProduct->id}");
            }
        }
        
        // ELIMINAR LA VENTA - NO TOCAR purchase->quantity
        $sale->delete();                  
        
        // Limpiar caché
        \Cache::forget("product_stock_{$productId}");
        
        // Calcular stock disponible dinámicamente        
        $product = Product::with(['purchase', 'sales'])->find($productId);         
        $remainingStock = $product ? $product->availableQuantity : 0;                  
        
        // Verificar si es la última venta del grupo         
        $remainingSales = Sale::where('sale_group_id', $saleGroupId)->count();                  
        
        DB::commit();                  
        
        return response()->json([             
            'success' => true,             
            'message' => $remainingSales > 0                  
                ? 'Venta eliminada correctamente'                 
                : 'Última venta del grupo eliminada',             
            'remaining_stock' => $remainingStock,             
            'remaining_sales' => $remainingSales,
            'deleted_quantity' => $quantitySold
        ]);              
    } catch (\Exception $e) {         
        DB::rollBack();         
        \Log::error("Error eliminando venta: ".$e->getMessage());         
        return response()->json([             
            'success' => false,             
            'message' => 'Error crítico: '.$e->getMessage()         
        ], 500);     
    } 
}

// MÉTODO 2: Eliminar venta individual (Route Model Binding)
public function destroyWithModel(Sale $sale)
{
    try {
        // Guardar información del grupo antes de eliminar
        $saleGroupId = $sale->sale_group_id;
        $customerId = $sale->customer_id;
        $productId = $sale->product_id;
        
        // SOLO ELIMINAR LA VENTA - NO TOCAR purchase->quantity
        $sale->delete();
        
        // Limpiar caché
        \Cache::forget("product_stock_{$productId}");
        
        // Verificar si quedan más ventas en el grupo
        $remainingSales = Sale::where('sale_group_id', $saleGroupId)
                              ->where('customer_id', $customerId)
                              ->count();
        
        $message = $remainingSales > 0 ? 
            'Venta eliminada correctamente' : 
            'Última venta del grupo eliminada';
            
        return redirect()->route('sales.index')->with('success', $message);
        
    } catch (\Exception $e) {
        \Log::error('Error eliminando venta: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error eliminando venta: ' . $e->getMessage());
    }
}

// MÉTODO 3: Eliminar grupo completo de ventas
public function destroyGroup(Request $request)
{
    \Log::info('Iniciando eliminación de grupo', ['data' => $request->all()]);

    // Validación
    $validated = $request->validate([
        'sale_group_id' => 'required|string|max:100',
        'customer_id' => 'required|integer|exists:customers,id'
    ]);

    try {
        \DB::beginTransaction();

        // 1. Obtener todas las ventas del grupo
        $sales = Sale::with(['product.purchase'])
            ->where('sale_group_id', $validated['sale_group_id'])
            ->where('customer_id', $validated['customer_id'])
            ->get();

        if ($sales->isEmpty()) {
            \Log::warning('No se encontraron ventas para eliminar', $validated);
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron ventas para este grupo/cliente'
            ], 404);
        }

        // 2. Guardar IDs de productos para limpiar caché
        $productIds = collect();
        $productsToDelete = collect(); // Productos que deben ser eliminados completamente

        // 3. Procesar cada venta y manejar transferencias
        foreach ($sales as $sale) {
            $productIds->push($sale->product_id);

            // Verificar si es una venta entre municipios diferentes
            if ($sale->sale_type === 'sale' && 
                $sale->origin_municipality && 
                $sale->destination_municipality && 
                $sale->origin_municipality !== $sale->destination_municipality) {
                
                \Log::info('Procesando venta inter-municipal', [
                    'sale_id' => $sale->id,
                    'origin' => $sale->origin_municipality,
                    'destination' => $sale->destination_municipality,
                    'quantity' => $sale->quantity,
                    'purchase_id' => $sale->product->purchase_id
                ]);

                // Buscar el producto duplicado en el municipio de destino
                $destinationProduct = \App\Models\Product::where('purchase_id', $sale->product->purchase_id)
                    ->where('municipality', $sale->destination_municipality)
                    ->first();

                if ($destinationProduct) {
                    \Log::info('Producto de destino encontrado', [
                        'destination_product_id' => $destinationProduct->id,
                        'municipality' => $destinationProduct->municipality
                    ]);

                    // Verificar si este producto de destino tiene ventas propias
                    $destinationSales = \App\Models\Sale::where('product_id', $destinationProduct->id)->count();
                    
                    \Log::info('Ventas del producto de destino', [
                        'destination_product_id' => $destinationProduct->id,
                        'sales_count' => $destinationSales
                    ]);

                    // Si no tiene ventas propias, debe ser eliminado completamente
                    if ($destinationSales == 0) {
                        $productsToDelete->push($destinationProduct);
                        \Log::info('Producto de destino marcado para eliminación completa', [
                            'product_id' => $destinationProduct->id
                        ]);
                    }

                    // Eliminar todos los InventoryAdjustments relacionados con esta transferencia
                    $adjustmentsDeleted = 0;
                    
                    // Eliminar transfer_out del producto origen
                    $transferOutAdjustments = \App\Models\InventoryAdjustment::where('product_id', $sale->product_id)
                        ->where('type', 'transfer_out')
                        ->where('quantity', $sale->quantity)
                        ->where('created_at', '>=', $sale->created_at->subMinutes(10))
                        ->where('created_at', '<=', $sale->created_at->addMinutes(10))
                        ->get();

                    foreach ($transferOutAdjustments as $adjustment) {
                        $adjustment->delete();
                        $adjustmentsDeleted++;
                        \Log::info('Eliminado transfer_out', ['adjustment_id' => $adjustment->id]);
                    }

                    // Eliminar transfer_in del producto destino
                    $transferInAdjustments = \App\Models\InventoryAdjustment::where('product_id', $destinationProduct->id)
                        ->where('type', 'transfer_in')
                        ->where('quantity', $sale->quantity)
                        ->where('created_at', '>=', $sale->created_at->subMinutes(10))
                        ->where('created_at', '<=', $sale->created_at->addMinutes(10))
                        ->get();

                    foreach ($transferInAdjustments as $adjustment) {
                        $adjustment->delete();
                        $adjustmentsDeleted++;
                        \Log::info('Eliminado transfer_in', ['adjustment_id' => $adjustment->id]);
                    }

                    \Log::info('Ajustes de inventario eliminados', ['total' => $adjustmentsDeleted]);

                    // Agregar producto de destino para limpiar caché
                    $productIds->push($destinationProduct->id);
                }
            }

            // Eliminar la venta
            $sale->delete();
            \Log::info('Venta eliminada', ['sale_id' => $sale->id]);
        }

        // 4. Eliminar productos duplicados que no tienen ventas propias
        foreach ($productsToDelete as $productToDelete) {
            // Verificar una vez más que no tiene ventas
            $salesCount = \App\Models\Sale::where('product_id', $productToDelete->id)->count();
            if ($salesCount == 0) {
                // Eliminar todos los InventoryAdjustments del producto
                \App\Models\InventoryAdjustment::where('product_id', $productToDelete->id)->delete();
                
                // Eliminar el producto
                $productToDelete->delete();
                \Log::info('Producto duplicado eliminado completamente', [
                    'product_id' => $productToDelete->id,
                    'municipality' => $productToDelete->municipality
                ]);
            }
        }

        // 5. Limpiar caché de todos los productos afectados
        foreach ($productIds->unique() as $productId) {
            \Cache::forget("product_stock_{$productId}");
        }

        \DB::commit();

        \Log::info('Eliminación completada con éxito', [
            'group_id' => $validated['sale_group_id'],
            'total_sales' => $sales->count(),
            'affected_products' => $productIds->unique()->count(),
            'deleted_products' => $productsToDelete->count()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Se eliminaron ' . $sales->count() . ' ventas correctamente' . 
                        ($productsToDelete->count() > 0 ? ' y ' . $productsToDelete->count() . ' ' : ''),
            'deleted_count' => $sales->count(),
            'affected_products' => $productIds->unique()->count(),
            'deleted_duplicate_products' => $productsToDelete->count()
        ]);

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Error crítico al eliminar', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error del servidor: ' . $e->getMessage()
        ], 500);
    }
}
}

