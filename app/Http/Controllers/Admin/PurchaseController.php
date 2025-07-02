<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
      public function index(Request $request)
{
    $title = 'purchases';
    if($request->ajax()){
        $query = Purchase::with(['category', 'supplier']);
        
        // Aplicar filtros
        $filter = $request->get('filter', 'all');
        switch($filter) {
            case 'active':
                $query->where('expiry_date', '>', now()->addDays(30));
                break;
            case 'near-expiry':
                $query->nearExpiry(30);
                break;
            case 'expired':
                $query->expired();
                break;
        }
        
        $purchases = $query->orderBy('expiry_date', 'asc')->get();
        
        return DataTables::of($purchases)
            ->addColumn('batch_number', function($purchase){
                $badge = '';
                $badgeClass = 'badge-active';
                
                if ($purchase->isExpired()) {
                    $badge = '<span class="badge badge-expired ml-1">Vencido</span>';
                    $badgeClass = 'text-danger';
                } elseif ($purchase->isNearExpiry()) {
                    $badge = '<span class="badge badge-near-expiry ml-1">Por vencer</span>';
                    $badgeClass = 'text-warning';
                }
                
                return '<span class="batch-number ' . $badgeClass . '" data-toggle="tooltip" title="Click para ver detalles" style="cursor: pointer;">' 
                       . $purchase->batch_number . '</span>' . $badge;
            })
            ->addColumn('product', function($purchase){
                $image = '';
                if(!empty($purchase->image)){
                    $image = '<span class="avatar avatar-sm mr-2">
                        <img class="avatar-img rounded" src="'.asset("storage/purchases/".$purchase->image).'" alt="product">
                    </span>';
                }                 
                return '<div class="d-flex align-items-center">' . $image . $purchase->product . '</div>';
            })
            ->addColumn('category', function($purchase){
                return $purchase->category ? 
                    '<span class="badge badge-info">' . $purchase->category->name . '</span>' : 
                    '<span class="text-muted">N/A</span>';
            })
            ->addColumn('cost_price', function($purchase){
                return '<span class="font-weight-bold">' . settings('app_currency','$') . ' ' . number_format($purchase->cost_price, 2) . '</span>';
            })
            ->addColumn('quantity', function($purchase){
                $available = $purchase->available_stock;
                $total = $purchase->quantity;
                
                $class = 'stock-good';
                $icon = 'fas fa-check-circle';
                
                if ($available == 0) {
                    $class = 'stock-critical';
                    $icon = 'fas fa-times-circle';
                } elseif ($available <= ($total * 0.2)) {
                    $class = 'stock-low';
                    $icon = 'fas fa-exclamation-triangle';
                }
                
                return '<div class="stock-info ' . $class . '">
                            <i class="' . $icon . '"></i>
                            <strong>' . $available . '</strong>/' . $total . '
                            <br><small>(' . round(($available/$total)*100, 1) . '% disponible)</small>
                        </div>';
            })
            ->addColumn('supplier', function($purchase){
                return $purchase->supplier ? 
                    '<span class="badge badge-secondary">' . $purchase->supplier->name . '</span>' : 
                    '<span class="text-muted">N/A</span>';
            })
            ->addColumn('expiry_date', function($purchase){
                $date = $purchase->expiry_date->format('d M, Y');
                $daysToExpiry = $purchase->expiry_date->diffInDays(now(), false);
                
                $class = '';
                $icon = '';
                $extraInfo = '';
                
                if ($purchase->isExpired()) {
                    $class = 'text-danger';
                    $icon = '<i class="fas fa-exclamation-triangle"></i> ';
                    $extraInfo = '<br><small>Vencido hace ' . abs($daysToExpiry) . ' días</small>';
                } elseif ($purchase->isNearExpiry()) {
                    $class = 'text-warning';
                    $icon = '<i class="fas fa-clock"></i> ';
                    $extraInfo = '<br><small>Vence en ' . $daysToExpiry . ' días</small>';
                } else {
                    $class = 'text-success';
                    $icon = '<i class="fas fa-calendar-check"></i> ';
                    $extraInfo = '<br><small>' . $daysToExpiry . ' días restantes</small>';
                }
                
                return '<div class="' . $class . '">' . $icon . $date . $extraInfo . '</div>';
            })
            ->addColumn('action', function ($row) {
                $viewbtn = '<a href="javascript:void(0)" class="btn btn-info btn-sm mr-1" onclick="viewBatchDetails(\'' . $row->batch_number . '\')" data-toggle="tooltip" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                           </a>';
                
                $editbtn = '<a href="'.route("purchases.edit", $row->id).'" class="btn btn-primary btn-sm mr-1" data-toggle="tooltip" title="Editar">
                            <i class="fas fa-edit"></i>
                           </a>';
                
                $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('purchases.destroy', $row->id).'" 
                             href="javascript:void(0)" id="deletebtn" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar">
                             <i class="fas fa-trash"></i>
                            </a>';
                
                if (!auth()->user()->hasPermissionTo('edit-purchase')) {
                    $editbtn = '';
                }
                if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                    $deletebtn = '';
                }
                
                $btn = $viewbtn . $editbtn . $deletebtn;
                return '<div class="btn-group" role="group">' . $btn . '</div>';
            })
            ->rawColumns(['batch_number', 'product', 'category', 'cost_price', 'quantity', 'supplier', 'expiry_date', 'action'])
            ->make(true);
    }
    return view('admin.purchases.index', compact('title'));
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function create()
    {
        $title = 'create purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.create', compact('title', 'categories', 'suppliers'));
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
            'product' => 'required|max:200',
            'category' => 'required|exists:categories,id',
            'cost_price' => 'required|numeric|min:0.01',
            'quantity' => 'required|integer|min:1',
            'expiry_date' => 'required|date|after:today',
            'supplier' => 'required|exists:suppliers,id',
            'image' => 'nullable|file|image|mimes:jpg,jpeg,png,gif|max:2048',
            'batch_number' => 'nullable|string|max:50|unique:purchases,batch_number',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $imageName = null;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        
        // Crear la compra con el lote
        $purchase = Purchase::create([
            'batch_number' => $request->batch_number, // Se auto-genera si está vacío
            'product' => $request->product,
            'category_id' => $request->category,
            'supplier_id' => $request->supplier,
            'cost_price' => $request->cost_price,
            'quantity' => $request->quantity,
            'expiry_date' => $request->expiry_date,
            'image' => $imageName,
            'notes' => $request->notes,
        ]);
        
        $notifications = notify("Lote {$purchase->batch_number} ha sido creado exitosamente");
        return redirect()->route('purchases.index')->with($notifications);
    }

   
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $title = 'edit purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        
        // Calcular cantidad usada basándose en la cantidad inicial
        $usedQuantity = $purchase->products()->count();
        $hasProducts = $usedQuantity > 0;
        $availableStock = $purchase->initial_quantity - $usedQuantity;
        
        return view('admin.purchases.edit',compact(
            'title','purchase','categories','suppliers','usedQuantity','hasProducts','availableStock'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
        $this->validate($request,[
            'product'=>'required|max:200',
            'category'=>'required',
            'cost_price'=>'required|min:1',
            'quantity'=>'required|min:1', // Validamos que sea mayor a 1
            'expiry_date'=>'required',
            'supplier'=>'required',
            'image'=>'file|image|mimes:jpg,jpeg,png,gif',
        ]);

        $imageName = $purchase->image;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        
        // Actualizamos todos los campos incluyendo quantity
        $purchase->update([
            'product'=>$request->product,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'cost_price'=>$request->cost_price,
            'quantity'=>$request->quantity, // Permitimos cambiar la cantidad comprada
            'expiry_date'=>$request->expiry_date,
            'image'=>$imageName,
        ]);
        
        $notifications = notify("Purchase has been updated");
        return redirect()->route('purchases.index')->with($notifications);
    }

    /**
     * Método para obtener el stock disponible de una compra
     */
    public function getAvailableStock($purchaseId)
    {
        $purchase = Purchase::findOrFail($purchaseId);
        $usedQuantity = $purchase->products()->count();
        return $purchase->quantity - $usedQuantity; // Usamos quantity (cantidad comprada)
    }

    /**
     * Método para verificar si se puede crear un producto desde esta compra
     */
    public function canCreateProduct($purchaseId)
    {
        return $this->getAvailableStock($purchaseId) > 0;
    }

    public function reports(){
        $title ='purchase reports';
        return view('admin.purchases.reports',compact('title'));
    }

   public function generateReport(Request $request){
    $this->validate($request,[
        'from_date' => 'required',
        'to_date' => 'required'
    ]);
    $title = 'purchases reports';
    
    $purchases = Purchase::with(['category', 'supplier'])
        ->whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))
        ->get();
    
    return view('admin.purchases.reports', compact('purchases', 'title'));
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
  
public function destroy(Request $request, $id = null)
{
    try {
        // Obtener el ID de la request o del parámetro
        $purchaseId = $id ?? $request->id ?? $request->input('id');
        
        if (!$purchaseId) {
            return response()->json([
                'success' => false,
                'message' => 'ID de compra no proporcionado.'
            ], 400);
        }
        
        $purchase = Purchase::find($purchaseId);
        
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'La compra no fue encontrada.'
            ], 404);
        }
        
        // Opcional: Verificar si la compra tiene productos asociados
        // Descomenta estas líneas si tienes relación con productos
        /*
        $usedQuantity = $purchase->products()->count();
        if ($usedQuantity > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar esta compra porque tiene productos asociados.'
            ]);
        }
        */
        
        // Eliminar imagen si existe
        if ($purchase->image && file_exists(public_path('storage/purchases/' . $purchase->image))) {
            unlink(public_path('storage/purchases/' . $purchase->image));
        }
        
        $batchNumber = $purchase->batch_number;
        $deleted = $purchase->delete();
        
        if ($deleted) {
            \Log::info("Compra eliminada exitosamente - ID: {$purchaseId}, Lote: {$batchNumber}");
            
            return response()->json([
                'success' => true,
                'message' => "El lote {$batchNumber} ha sido eliminado exitosamente."
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el registro.'
            ], 500);
        }
        
    } catch (\Exception $e) {
        \Log::error('Error al eliminar compra: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor: ' . $e->getMessage()
        ], 500);
    }
}
}