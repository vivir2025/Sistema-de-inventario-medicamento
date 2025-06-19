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
            $purchases = Purchase::get();
            return DataTables::of($purchases)
                ->addColumn('product',function($purchase){
                    $image = '';
                    if(!empty($purchase->image)){
                        $image = '<span class="avatar avatar-sm mr-2">
						<img class="avatar-img" src="'.asset("storage/purchases/".$purchase->image).'" alt="product">
					    </span>';
                    }                 
                    return $purchase->product.' ' . $image;
                })
                ->addColumn('category',function($purchase){
                    if(!empty($purchase->category)){
                        return $purchase->category->name;
                    }
                })
                ->addColumn('cost_price',function($purchase){
                    return settings('app_currency','$'). ' '. $purchase->cost_price;
                })
               ->addColumn('quantity',function($purchase){
    // Mostrar solo la cantidad comprada original
    return $purchase->quantity;
})
                ->addColumn('supplier',function($purchase){
                    return $purchase->supplier->name;
                })
                ->addColumn('expiry_date',function($purchase){
                    return date_format(date_create($purchase->expiry_date),'d M, Y');
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("purchases.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('purchases.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-purchase')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $btn = $editbtn.' '.$deletebtn;
                    return $btn;
                })
                ->rawColumns(['product','action','quantity'])
                ->make(true);
        }
        return view('admin.purchases.index',compact(
            'title'
        ));
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
        return view('admin.purchases.create',compact(
            'title','categories','suppliers'
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
            'category'=>'required',
            'cost_price'=>'required|min:1',
            'quantity'=>'required|min:1',
            'expiry_date'=>'required',
            'supplier'=>'required',
            'image'=>'file|image|mimes:jpg,jpeg,png,gif',
        ]);
        
        $imageName = null;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        
        // Solo guardamos la cantidad comprada - NUNCA se modifica
        Purchase::create([
            'product'=>$request->product,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'cost_price'=>$request->cost_price,
            'quantity'=>$request->quantity, // Cantidad COMPRADA (fija)
            'expiry_date'=>$request->expiry_date,
            'image'=>$imageName,
        ]);
        
        $notifications = notify("Purchase has been added");
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
    public function destroy(Request $request)
    {
        return Purchase::findOrFail($request->id)->delete();
    }
}