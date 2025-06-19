<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\CustomerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware(['auth'])->prefix('admin')->group(function(){
    Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
    Route::get('',[DashboardController::class,'Index']);
    Route::get('notification',[NotificationController::class,'markAsRead'])->name('mark-as-read');
    Route::get('notification-read',[NotificationController::class,'read'])->name('read');
    Route::get('profile',[UserController::class,'profile'])->name('profile');
    Route::post('profile/{user}',[UserController::class,'updateProfile'])->name('profile.update');
    Route::put('profile/update-password/{user}',[UserController::class,'updatePassword'])->name('update-password');
    Route::post('logout',[LogoutController::class,'index'])->name('logout');

    Route::resource('users',UserController::class);
    Route::resource('permissions',PermissionController::class)->only(['index','store','destroy']);
    Route::put('permission',[PermissionController::class,'update'])->name('permissions.update');
    Route::resource('roles',RoleController::class);
    Route::resource('suppliers',SupplierController::class);
    Route::resource('categories',CategoryController::class)->only(['index','store','destroy']);
    Route::put('categories',[CategoryController::class,'update'])->name('categories.update');
    Route::resource('purchases',PurchaseController::class)->except('show');
    Route::get('purchases/reports',[PurchaseController::class,'reports'])->name('purchases.report');
    Route::post('purchases/reports',[PurchaseController::class,'generateReport']);
    Route::resource('products',ProductController::class)->except('show');
    Route::get('products/outstock',[ProductController::class,'outstock'])->name('outstock');
    Route::get('products/expired',[ProductController::class,'expired'])->name('expired');
    Route::get('/expired', [ProductController::class, 'expired'])->name('expired');
    Route::resource('sales',SaleController::class)->except('show');
    Route::get('sales/reports',[SaleController::class,'reports'])->name('sales.report');
    Route::post('sales/reports',[SaleController::class,'generateReport']);

    Route::get('backup', [BackupController::class,'index'])->name('backup.index');
    Route::put('backup/create', [BackupController::class,'create'])->name('backup.store');
    Route::get('backup/download/{file_name?}', [BackupController::class,'download'])->name('backup.download');
    Route::delete('backup/delete/{file_name?}', [BackupController::class,'destroy'])->where('file_name', '(.*)')->name('backup.destroy');

    Route::get('settings',[SettingController::class,'index'])->name('settings');
});

Route::middleware(['guest'])->prefix('admin')->group(function () {
    Route::get('',[DashboardController::class,'Index']);

    Route::get('login',[LoginController::class,'index'])->name('login');
    Route::post('login',[LoginController::class,'login']);

    Route::get('register',[RegisterController::class,'index'])->name('register');
    Route::post('register',[RegisterController::class,'store']);

    Route::get('forgot-password',[ForgotPasswordController::class,'index'])->name('password.request');
    Route::post('forgot-password',[ForgotPasswordController::class,'requestEmail']);
    Route::get('reset-password/{token}',[ResetPasswordController::class,'index'])->name('password.reset');
    Route::post('reset-password',[ResetPasswordController::class,'resetPassword'])->name('password.update');
});

    //customer
    Route::get('/add-customer', function () {
        return view('Admin.add_customer');
    })->middleware(['auth'])->name('add.customer');


Route::post('/insert-customer', [CustomerController::class, 'store'])->middleware(['auth'])->name('insert.customer');

  Route::get('/all-customers',[CustomerController::class,'customersData'])->middleware(['auth'])->name('all.customers');

Route::get('/edit-customer/{id}', [CustomerController::class, 'edit'])->middleware(['auth'])->name('edit.customer');

Route::post('/update-customer/{id}', [CustomerController::class, 'update'])->middleware(['auth'])->name('update.customer');

Route::delete('/delete-customer/{id}', [CustomerController::class, 'delete'])->middleware(['auth'])->name('delete.customer');

Route::post('/delete-customer/{id}',[CustomerController::class,'destroy'])->middleware(['auth'])->name('delete.customer');


    
Route::get('sales/{id}/invoice', [SaleController::class, 'generateInvoice'])->name('sales.invoice');
// Agregar esta ruta en tu archivo web.php o routes/web.php
Route::post('/sales/repair-groups', [SaleController::class, 'repairSaleGroups'])
    ->name('sales.repair-groups');

    // Para el método que recibe ID por POST
Route::post('sales/destroy', [SaleController::class, 'destroy'])->name('sales.destroy');


// Agregar esta ruta en tu archivo web.php o routes/web.php
Route::delete('/sales/destroy-group', [SaleController::class, 'destroyGroup'])->name('sales.destroy-group');
    

// Agregar esta ruta en tu archivo web.php o routes/web.php
Route::delete('/sales/destroy-group', [SaleController::class, 'destroyGroup'])->name('sales.destroy-group');
    
// Si también quieres la ruta de factura que se menciona en el código:
Route::get('/sales/{id}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
Route::get('/', function () {
    return view('Admin.Auth.login');
});

Route::put('sales/update-group/{saleGroupId}', [SaleController::class, 'updateGroup'])
    ->name('sales.update-group');
// Ruta para la factura grupal
Route::get('sales/invoice/{sale_group_id}', [SaleController::class, 'invoiceGroup'])
     ->name('sales.invoice-group');
//product
Route::get('/add-product', function () {
    return view('Admin.add_product');
})->middleware(['auth'])->name('add.product');

Route::post('/insert-product',[ProductController::class,'store'])->middleware(['auth']);

Route::get('/all-product',[ProductController::class,'allProduct'])->middleware(['auth'])->name('all.product');

Route::get('/available-products',[ProductController::class,'availableProducts'])->middleware(['auth'])->name('available.products');

Route::get('/purchase-products/{id}', [ProductController::class,'purchaseData'])->middleware(['auth']);

Route::post('/insert-purchase-products',[ProductController::class,'storePurchase'])->middleware(['auth']);
Route::get('edit-product/{id}', [ProductController::class, 'edit'])->name('edit.product');
Route::delete('products/{product}', [ProductController::class, 'destroy'])
    ->name('products.destroy')
    ->middleware(['auth', 'can:destroy-product']);
Route::put('/update-product/{id}', [ProductController::class, 'update'])->middleware(['auth'])->name('update.product');
Route::get('/add-order', [ProductController::class, 'addOrderForm'])->name('add.order');
Route::post('/insert-new-order', [ProductController::class, 'insertNewOrder'])->name('insert.order');

//invoice
Route::get('/add-invoice/{id}', [InvoiceController::class,'formData'])->middleware(['auth']);

Route::get('/new-invoice', [InvoiceController::class,'newformData'])->middleware(['auth'])->name('new.invoice');

Route::post('/insert-invoice',[InvoiceController::class,'store'])->middleware(['auth']);

Route::get('/invoice-details', function () {
    return view('Admin.invoice_details');
})->middleware(['auth'])->name('invoice.details');

Route::get('/all-invoice', [InvoiceController::class,'allInvoices'])->middleware(['auth'])->name('all.invoices');

Route::get('/sold-products',[InvoiceController::class,'soldProducts'])->middleware(['auth'])->name('sold.products');
// Route::get('/delete', [InvoiceController::class,'delete']);


//order
Route::get('/add-order/{name}', [ProductController::class,'formData'])->middleware(['auth'])->name('add.order');

Route::post('/insert-order',[OrderController::class,'store'])->middleware(['auth']);

Route::get('/all-orders',[OrderController::class,'ordersData'])->middleware(['auth'])->name('all.orders');

Route::get('/pending-orders',[OrderController::class,'pendingOrders'])->middleware(['auth'])->name('pending.orders');

Route::get('/delivered-orders',[OrderController::class,'deliveredOrders'])->middleware(['auth'])->name('delivered.orders');

Route::get('/new-order', [OrderController::class,'newformData'])->middleware(['auth'])->name('new.order');

Route::post('/insert-new-order',[OrderController::class,'newStore'])->middleware(['auth']);

