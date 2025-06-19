<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Mostrar todos los clientes
     */
    public function customersData()
    {
        $customers = Customer::all();
        return view('Admin.all_customers', compact('customers')); // Asegúrate que la vista exista
    }
    public function create()
{
    return view('customer.create');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:customers',
        'company' => 'nullable|string',
        'address' => 'nullable|string',
        'phone' => 'nullable|string'
    ]);

    Customer::create($validated);
    return redirect()->route('add.customer')->with('success', 'Cliente creado exitosamente');
}


   

    /**
     * Mostrar el formulario para editar un cliente
     */
    public function edit($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            // CORREGIR LA RUTA DE LA VISTA
            return view('Admin.edit_customer', compact('customer')); // Cambiado de 'customer.edit_customer'
        } catch (\Exception $e) {
            return redirect()->route('all.customers')->with('error', 'Cliente no encontrado');
        }
    }

    /**
     * Actualizar un cliente existente
     */
    public function update(Request $request, $id) // CORREGIR EL ORDEN DE PARÁMETROS
    {
        try {
            $customer = Customer::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customers,email,' . $id,
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'phone' => 'nullable|string|max:20'
            ]);

            $customer->update($validated);

            return redirect()->route('all.customers')->with('success', 'Cliente actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar el cliente: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un cliente
     */
    public function delete($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->delete();

            return redirect()->route('all.customers')->with('success', 'Cliente eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el cliente: ' . $e->getMessage());
        }
    }

    public function destroy($id)
{
    try {
        $customer = Customer::find($id);
        
        if (!$customer) {
            return redirect('/all-customers')->with('error', 'Cliente no encontrado');
        }
        
        $customer->delete();
        
        return redirect('/all-customers')->with('success', 'Cliente eliminado exitosamente');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error al eliminar el cliente');
    }
}

    /**
     * Mostrar el dashboard con clientes
     */
    public function index()
    {
        $customers = Customer::with('sales')->get();
        return view('dashboard.dashboard', compact('customers')); // Corregir nombre de vista
    }
}