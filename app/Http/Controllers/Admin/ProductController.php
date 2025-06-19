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
    if ($request->ajax()) {
        $products = Product::with(['purchase.category', 'sales'])->latest();
        
        return DataTables::of($products)
            ->addColumn('product', function($product) {
                if (!$product->purchase) return 'N/A';
                
                $image = $product->purchase->image ? 
                    '<span class="avatar avatar-sm mr-2">
                        <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'">
                    </span>' : '';
                
                return $product->purchase->product . ' ' . $image;
            })
            ->addColumn('category', function($product) {
                return $product->purchase->category->name ?? 'N/A';
            })
            ->addColumn('price', function($product) {
                return settings('app_currency', '$').' '.number_format($product->price, 2);
            })
            ->addColumn('quantity', function($product) {
                if (!$product->purchase) return 'N/A';
                
                // MISMA LÓGICA QUE EN SALESCONTROLLER - Cálculo dinámico del stock
                $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
                $available = $product->purchase->quantity - $already_sold;
                
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
            // ESTA ES LA PARTE CLAVE - Configurar la búsqueda
            ->filterColumn('product', function($query, $keyword) {
                $query->whereHas('purchase', function($q) use ($keyword) {
                    $q->where('product', 'LIKE', "%{$keyword}%");
                });
            })
            ->filterColumn('category', function($query, $keyword) {
                $query->whereHas('purchase.category', function($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
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
            // Configurar búsqueda global
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $searchValue = $request->search['value'];
                    $query->where(function($q) use ($searchValue) {
                        // Buscar en el nombre del producto
                        $q->whereHas('purchase', function($subQ) use ($searchValue) {
                            $subQ->where('product', 'LIKE', "%{$searchValue}%");
                        })
                        // Buscar en la categoría
                        ->orWhereHas('purchase.category', function($subQ) use ($searchValue) {
                            $subQ->where('name', 'LIKE', "%{$searchValue}%");
                        })
                        // Buscar en precio
                        ->orWhere('price', 'LIKE', "%{$searchValue}%")
                        // Buscar en descuento
                        ->orWhere('discount', 'LIKE', "%{$searchValue}%")
                        // Buscar en fecha de vencimiento
                        ->orWhereHas('purchase', function($subQ) use ($searchValue) {
                            $subQ->where('expiry_date', 'LIKE', "%{$searchValue}%");
                        });
                    });
                }
            })
            ->rawColumns(['product', 'quantity', 'action'])
            ->make(true);
    }
    
    return view('admin.products.index', compact('title'));
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
            'price'=>'required|min:1',
            'discount'=>'nullable',
            'description'=>'nullable|max:255',
        ]);
        $price = $request->price;
        if($request->discount >0){
           $price = $request->discount * $request->price;
        }
        Product::create([
            'purchase_id'=>$request->product,
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
    
    if($request->ajax()){
        try {
            // Consulta modificada para solo productos vencidos (expiry_date < hoy)
            $purchases = Purchase::whereNotNull('expiry_date')
                ->where('expiry_date', '<', now()) // Solo fechas pasadas
                ->with(['category', 'supplier'])
                ->get();
            
            return DataTables::of($purchases)
                ->addColumn('product', function($purchase) {
                    $image = '';
                    if(!empty($purchase->image)) {
                        $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$purchase->image).'" alt="image">
                            </span>';
                    }
                    return ($purchase->product ?? 'Sin nombre') . ' ' . $image;
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
                ->addColumn('action', function ($purchase) {
                    $product = \App\Models\Product::where('purchase_id', $purchase->id)->first();
                    $productId = $product ? $product->id : $purchase->id;
                    
                    $editbtn = '<a href="'.route("products.edit", $productId).'" class="editbtn"><button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$productId.'" data-route="'.route('products.destroy', $productId).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></a>';
                    
                    $btn = $editbtn . ' ' . $deletebtn;
                    return $btn;
                })
                ->rawColumns(['product', 'quantity', 'action'])
                ->make(true);
                
        } catch(\Exception $e) {
            \Log::error('Error in expired method: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al cargar los datos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    return view('admin.products.expired', compact('title'));
}

/**
 * Display a listing of out of stock resources.
 *
 * @param  \Illuminate\Http\Request $request
 * @return \Illuminate\Http\Response
 */
public function outstock(Request $request)
{
    $title = "outstocked Products";
    
    if($request->ajax()){
        // Obtener TODOS los productos para filtrar dinámicamente los que están sin stock
        $products = Product::with(['purchase.category', 'purchase.supplier'])->get();
        
        // Filtrar productos que realmente están sin stock (stock dinámico <= 0)
        $outOfStockProducts = $products->filter(function($product) {
            if (!$product->purchase) return false;
            
            $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
            $available = $product->purchase->quantity - $already_sold;
            
            return $available <= 0;
        });
        
        return DataTables::of($outOfStockProducts)
            ->addColumn('product', function($product) {
                $image = '';
                if(!empty($product->purchase)) {
                    if(!empty($product->purchase->image)) {
                        $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'" alt="image">
                            </span>';
                    }
                    return $product->purchase->product . ' ' . $image;
                }
                return '';
            })
            ->addColumn('category', function($product) {
                $category = null;
                if(!empty($product->purchase->category)) {
                    $category = $product->purchase->category->name;
                }
                return $category;
            })
            ->addColumn('price', function($product) {
                return settings('app_currency','$') . ' ' . $product->price;
            })
            ->addColumn('quantity', function($product) {
                // CÁLCULO DINÁMICO DE STOCK DISPONIBLE
                if(!empty($product->purchase)) {
                    $already_sold = \App\Models\Sale::where('product_id', $product->id)->sum('quantity');
                    $available = $product->purchase->quantity - $already_sold;
                    
                    return '<span class="text-danger">
                        '.$available.'
                    </span>';
                }
                return '<span class="text-danger">0</span>';
            })
            ->addColumn('discount', function($product) {
                return $product->discount ?? '0.00';
            })
            ->addColumn('expiry_date', function($product) {
                if(!empty($product->purchase) && !empty($product->purchase->expiry_date)) {
                    return date_format(date_create($product->purchase->expiry_date), 'd M, Y');
                }
                return '';
            })
            ->addColumn('action', function ($row) {
                $editbtn = '<a href="'.route("products.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                
                if (!auth()->user()->hasPermissionTo('edit-product')) {
                    $editbtn = '';
                }
                if (!auth()->user()->hasPermissionTo('destroy-product')) {
                    $deletebtn = '';
                }
                
                $btn = $editbtn . ' ' . $deletebtn;
                return $btn;
            })
            ->rawColumns(['product', 'quantity', 'action'])
            ->make(true);
    }
    
    return view('admin.products.outstock', compact('title'));
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
