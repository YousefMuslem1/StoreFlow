<?php

namespace App\Http\Controllers;

use App\Enums\TransictionsType;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        // Get all suppliers with pagination
        $suppliers = Supplier::with('supplierBalance')
            ->withSum(['supplierTransactions as total_fixed_money' => function ($query) {
                $query->where('type', TransictionsType::MONEY)->whereNotNull('price_per_gram');
            }], 'amount') // المال المثبت
            ->withSum(['supplierTransactions as total_unfixed_money' => function ($query) {
                $query->where('type', TransictionsType::MONEY)->whereNull('price_per_gram');
            }], 'amount') // المال غير المثبت
            ->withSum(['supplierTransactions as total_fixed_gold' => function ($query) {
                $query->where('type', TransictionsType::GOLD)->whereNotNull('price_per_gram');
            }], 'received_weight') // الذهب المثبت
            ->withSum(['supplierTransactions as total_unfixed_gold' => function ($query) {
                $query->where('type', TransictionsType::GOLD)->whereNull('price_per_gram');
            }], 'received_weight') // الذهب غير المثبت
            ->paginate(10); // عرض البيانات مع pagination
        // dd($suppliers);
        return view('suppliers.index', compact('suppliers'));
    }

    // create a new supplier
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:suppliers,name',
        ]);

        try {
            $supplier = Supplier::create([
                'name' => $request->name,
                'contact_info' => $request->contact_info,
            ]);

            return response()->json([
                'success' => true,
                'name' => $supplier->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة المورد.',
            ], 500);
        }
    }

    // عرض عمليات المورد
    public function showTransactions($supplierId)
{
    // Retrieve the supplier and their transactions
    $supplier = Supplier::with('supplierTransactions.user')->findOrFail($supplierId);

    // Retrieve transactions for the supplier
    $transactions = $supplier->supplierTransactions()->orderBy('created_at', 'desc')->paginate(10);

    // Pass the supplier and transactions to the view
    return view('suppliers.transactions', compact('supplier', 'transactions'));
}
}
