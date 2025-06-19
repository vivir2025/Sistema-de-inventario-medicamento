<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Str;
use App\Models\Sale;
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
            ->select('sale_group_id', 'customer_id', 'created_at', 'ubicacion')
            ->selectRaw('COUNT(*) as product_count')
            ->selectRaw('SUM(total_price) as total_amount')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('MIN(id) as first_sale_id')
            ->groupBy('sale_group_id', 'customer_id', 'created_at', 'ubicacion');

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
                    ->where('created_at', $saleGroup->created_at)
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
                                'image' => $sale->product->purchase->image ?? null
                            ];
                        }
                        $groupedProducts[$productName]['quantity'] += $sale->quantity;
                    }
                }
                
                // Construir la lista de productos agrupados
                $productList = '';
                foreach($groupedProducts as $name => $data) {
                    $image = '';
                    if(!empty($data['image'])) {
                        $image = '<span class="avatar avatar-sm mr-1">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$data['image']).'" alt="image">
                            </span>';
                    }
                    $productList .= '<div class="product-item mb-1">
                        ' . $image . $name . ' 
                        <span class="badge badge-secondary ml-1">Qty: ' . $data['quantity'] . '</span>
                    </div>';
                }
                
                // Agregar los nombres de productos como atributo oculto para búsqueda
                $productList .= '<span style="display:none;">'.implode(' ', $productNames).'</span>';
                
                return $productList ?: 'Sin productos';
            })
            ->addColumn('customer', function($saleGroup){
                return $saleGroup->customer_name ?? 'Cliente no encontrado';
            })
            ->addColumn('ubicacion', function($saleGroup) {
                // Mapea los valores para mostrar nombres legibles
                $ubicaciones = [
                    'cajibio' => 'Cajibío',
                    'piendamo' => 'Piendamó', 
                    'morales' => 'Morales',
                    'administrativo' => 'Administrativo'
                ];
                return $ubicaciones[$saleGroup->ubicacion] ?? $saleGroup->ubicacion;
            })
            ->addColumn('total_price', function($saleGroup){                   
                return settings('app_currency','$').' '. number_format($saleGroup->total_amount, 2);
            })
            ->addColumn('total_quantity', function($saleGroup){                   
                return $saleGroup->total_quantity . ' items';
            })
            ->addColumn('date', function($saleGroup){
                return date_format(date_create($saleGroup->created_at),'d M, Y');
            })
            ->addColumn('action', function ($saleGroup) {
                // Usar el primer ID de venta del grupo para las acciones
                $firstSale = Sale::where('sale_group_id', $saleGroup->sale_group_id)
                    ->where('customer_id', $saleGroup->customer_id)
                    ->first();
                
                if (!$firstSale) {
                    return 'N/A';
                }
                
                $editbtn = '<a href="'.route("sales.edit", $firstSale->id).'" class="editbtn" title="Editar"><button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button></a>';
                
                // Verificar que sale_group_id no sea null
                $saleGroupIdValue = $saleGroup->sale_group_id ?? 'null';
                $customerIdValue = $saleGroup->customer_id ?? 'null';
                
                $deletebtn = '<button id="deletebtn" 
                                data-group-id="'.$saleGroupIdValue.'" 
                                data-customer-id="'.$customerIdValue.'" 
                                data-route="'.route('sales.destroy-group').'" 
                                class="btn btn-danger btn-sm" 
                                title="Eliminar grupo de ventas">
                                <i class="fas fa-trash"></i>
                              </button>';
                
               $invoicebtn = '<a href="'.route('sales.invoice-group', $saleGroup->sale_group_id).'" target="_blank" class="btn btn-success btn-sm" title="Generar factura"><i class="fas fa-file-invoice"></i></a>';
                
                // Verificar permisos
                if (!auth()->user()->hasPermissionTo('edit-sale')) {
                    $editbtn = '';
                }
                if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                    $deletebtn = '';
                }
                
                $btn = $invoicebtn.' '.$editbtn.' '.$deletebtn;
                return $btn;
            })
            // Configurar filtros personalizados para mejorar la búsqueda
            ->filterColumn('customer', function($query, $keyword) {
                $query->where('customers.name', 'like', "%{$keyword}%")
                      ->orWhere('customers.email', 'like', "%{$keyword}%")
                      ->orWhere('customers.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('ubicacion', function($query, $keyword) {
                $ubicaciones = [
                    'cajibio' => 'Cajibío',
                    'piendamo' => 'Piendamó', 
                    'morales' => 'Morales',
                    'administrativo' => 'Administrativo'
                ];
                
                // Buscar tanto por el valor original como por el nombre legible
                $query->where(function($q) use ($keyword, $ubicaciones) {
                    $q->where('grouped_sales.ubicacion', 'like', "%{$keyword}%");
                    
                    // Buscar en los nombres legibles
                    foreach($ubicaciones as $key => $value) {
                        if(stripos($value, $keyword) !== false) {
                            $q->orWhere('grouped_sales.ubicacion', $key);
                        }
                    }
                });
            })
            ->filterColumn('products', function($query, $keyword) {
                // Filtrar por productos usando una subconsulta
                $query->whereExists(function($subQuery) use ($keyword) {
                    $subQuery->select(DB::raw(1))
                             ->from('sales')
                             ->join('products', 'sales.product_id', '=', 'products.id')
                             ->join('purchases', 'products.purchase_id', '=', 'purchases.id')
                             ->whereColumn('sales.sale_group_id', 'grouped_sales.sale_group_id')
                             ->where('purchases.product', 'like', "%{$keyword}%");
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
            ->rawColumns(['products','action'])
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
    $rawProducts = Product::with(['purchase'])->get();
    
    $products = $rawProducts->map(function($product) {
        if ($product->purchase) {
            $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
            $available_stock = $product->purchase->quantity - $already_sold;
            $product->available_stock = $available_stock;
        } else {
            $product->available_stock = 0;
        }
        return $product;
    });
    
    $products = $products->filter(function($product) {
        return $product->purchase && $product->available_stock > 0;
    });
    
    $customers = Customer::all();
    $ubicaciones = ['cajibio' => 'Cajibío', 'piendamo' => 'Piendamó', 'morales' => 'Morales', 'administrativo' => 'Administrativo'];
    
    return view('admin.sales.create', compact(
        'title', 'products', 'customers', 'ubicaciones'
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
        'products' => 'required|array|min:1',
        'products.*.product_id' => 'required|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1',
        'total_price' => 'required|numeric|min:0',
        'ubicacion' => 'required|in:cajibio,piendamo,morales,administrativo'
    ]);
    
    $customer_id = $request->customer_id;
    $products = $request->products;
    $errors = [];
    $successful_sales = [];
    
    // Generar un ID único para agrupar estas ventas
    $sale_group_id = \Illuminate\Support\Str::uuid()->toString();
    
    // Verificar disponibilidad de stock para todos los productos antes de procesar
    foreach ($products as $index => $productData) {
        $product = Product::find($productData['product_id']);
        
        if (!$product || !$product->purchase) {
            $errors[] = "Producto no encontrado o sin compra asociada.";
            continue;
        }
        
        $purchased_item = $product->purchase;
        
        // Calcular cantidad ya vendida de este producto
        $already_sold = Sale::where('product_id', $product->id)->sum('quantity');
        $available = $purchased_item->quantity - $already_sold;
        
        if ($productData['quantity'] > $available) {
            $errors[] = "El producto '{$purchased_item->product}' no tiene suficiente stock. Disponible: {$available}, Solicitado: {$productData['quantity']}";
        }
    }
    
    if (!empty($errors)) {
        return redirect()->back()
            ->withErrors($errors)
            ->withInput();
    }
    
    // Usar transacción para asegurar consistencia
    \DB::transaction(function() use ($products, $customer_id, $sale_group_id, $request, &$successful_sales) {
        foreach ($products as $index => $productData) {
            $product = Product::find($productData['product_id']);
            $purchased_item = $product->purchase;
            $product_total_price = $productData['quantity'] * $product->price;
            
            $sale = Sale::create([
                'product_id' => $productData['product_id'],
                'customer_id' => $customer_id,
                'quantity' => $productData['quantity'],
                'total_price' => $product_total_price,
                'sale_group_id' => $sale_group_id,
                'ubicacion' => $request->ubicacion
            ]);
            
            $successful_sales[] = $sale;
            
            // Verificar stock bajo (calculando dinámicamente)
            $already_sold = Sale::where('product_id', $product->id)->sum('quantity');
            $remaining = $purchased_item->quantity - $already_sold;
            
            if ($remaining <= 1 && $remaining > 0) {
                event(new PurchaseOutStock($purchased_item));
            }
        }
    });
    
    $notification = '';
    if (count($successful_sales) > 0) {
        $product_count = count($successful_sales);
        $notification = notify("Venta registrada exitosamente con {$product_count} producto(s). Grupo ID: {$sale_group_id}");
        
        // Verificación de bajo stock para notificación
        $low_stock_notified = false;
        foreach ($successful_sales as $sale) {
            $product = $sale->product;
            $already_sold = Sale::where('product_id', $product->id)->sum('quantity');
            $remaining = $product->purchase->quantity - $already_sold;
            
            if ($remaining <= 1 && $remaining > 0) {
                $low_stock_notified = true;
                break;
            }
        }
        
        if ($low_stock_notified) {
            $notification = notify("Venta registrada. ¡Advertencia: Algunos productos están agotándose!");
        }
    }
    
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
    $products = Product::get();
    $customers = Customer::all();
    $ubicaciones = ['cajibio' => 'Cajibío', 'piendamo' => 'Piendamó', 'morales' => 'Morales', 'administrativo' => 'Administrativo'];
    
    $groupSales = Sale::with(['product.purchase', 'customer'])
        ->where('sale_group_id', $sale->sale_group_id)
        ->where('customer_id', $sale->customer_id)
        ->orderBy('created_at', 'asc')
        ->get();
    
    return view('admin.sales.edit', compact(
        'title', 'sale', 'products', 'customers', 'groupSales', 'ubicaciones'
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
        'ubicacion' => 'required|in:cajibio,piendamo,morales,administrativo'
    ]);
    
    $sold_product = Product::find($request->product);
    
    if (!$sold_product || !$sold_product->purchase) {
        return redirect()->back()->with('error', 'Product not found or has no purchase record.');
    }
    
    $purchased_item = Purchase::find($sold_product->purchase->id);
    
    // Si es el mismo producto, calcular stock disponible excluyendo esta venta actual
    if ($sale->product_id == $request->product) {
        // Calcular cantidad ya vendida EXCLUYENDO esta venta que estamos editando
        $already_sold = Sale::where('product_id', $sold_product->id)
                           ->where('id', '!=', $sale->id) // Excluir la venta actual
                           ->sum('quantity');
        
        $available_stock = $purchased_item->quantity - $already_sold;
        
        if ($request->quantity > $available_stock) {
            return redirect()->back()->with('error', 'Insufficient stock. Available quantity: ' . $available_stock);
        }
    } else {
        // Si cambió de producto, verificar stock del nuevo producto
        $already_sold_new_product = Sale::where('product_id', $sold_product->id)->sum('quantity');
        $available_stock_new_product = $purchased_item->quantity - $already_sold_new_product;
        
        if ($request->quantity > $available_stock_new_product) {
            return redirect()->back()->with('error', 'Insufficient stock for new product. Available quantity: ' . $available_stock_new_product);
        }
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
        'ubicacion' => $request->ubicacion
    ]);

    $notification = notify("Sale has been updated successfully");
    
    // Verificar stock bajo después de la actualización
    if ($sale->product_id == $request->product) {
        // Recalcular para el mismo producto
        $already_sold_after = Sale::where('product_id', $sold_product->id)->sum('quantity');
        $remaining = $purchased_item->quantity - $already_sold_after;
    } else {
        // Para producto nuevo
        $already_sold_after = Sale::where('product_id', $sold_product->id)->sum('quantity');
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
        
        // SOLO ELIMINAR LA VENTA - NO TOCAR purchase->quantity
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
        $productIds = $sales->pluck('product_id')->unique();

        // 3. SOLO ELIMINAR LAS VENTAS - NO TOCAR purchase->quantity
        foreach ($sales as $sale) {
            $sale->delete();
            \Log::info('Venta eliminada', ['sale_id' => $sale->id]);
        }

        // 4. Limpiar caché de todos los productos afectados
        foreach ($productIds as $productId) {
            \Cache::forget("product_stock_{$productId}");
        }

        \DB::commit();

        \Log::info('Eliminación completada con éxito', [
            'group_id' => $validated['sale_group_id'],
            'total_sales' => $sales->count()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Se eliminaron ' . $sales->count() . ' ventas correctamente',
            'deleted_count' => $sales->count(),
            'affected_products' => $productIds->count()
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

