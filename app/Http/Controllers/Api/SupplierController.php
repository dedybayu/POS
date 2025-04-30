<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierModel;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return SupplierModel::all();
    }

    public function store(Request $request)
    {
        $supplier = SupplierModel::create($request->all());
        return response()->json($supplier, 201);
    }

    public function show(SupplierModel $supplier)
    {
        return $supplier;
    }

    public function update(Request $request, SupplierModel $supplier)
    {
        $supplier->update($request->all());
        return $supplier;
    }
    

    public function destroy(SupplierModel $supplier)
    {
        $supplier->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data terhapus',
        ]);
    }
}
