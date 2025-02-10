<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Installment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    //
    public function index()
    {

        $customers = Customer::whereHas('installments')->with(['installments' => function ($query) {
            $query->select('customer_id', \DB::raw('SUM(amount_paid) as total_paid'))
                ->groupBy('customer_id');
        }])
            ->withCount(['installments as total_products' => function ($query) {
                $query->select(DB::raw('COUNT(DISTINCT product_id)'));
            }])->orderBy('updated_at', 'DESC')

            ->paginate(10);

        $customers->getCollection()->transform(function ($customer) {
            $totalSelledPrice = Product::whereIn('id', function ($query) use ($customer) {
                $query->select('product_id')
                    ->from('installments')
                    ->where('customer_id', $customer->id);
            })->sum('selled_price');

            $totalPayments = $customer->installments->sum('total_paid');
            $remainingAmount = $totalSelledPrice - $totalPayments;

            $customer->total_selled_price = $totalSelledPrice;
            $customer->total_payments = $totalPayments;
            $customer->remaining_amount = $remainingAmount;

            return $customer;
        });
        return view('employer.customer.index', compact('customers'));
    }

    public function getCustomerinfos(Request $request)
    {
        $customer = Customer::where('name', '=', $request->customer)->orWhere('phone', '=', $request->customer)->first();
        return response()->json($customer, 201);
    }

    public function customerInstallments(Request $request)
    {
        $installments = Installment::with(['product.type', 'customer'])->where('customer_id', $request->id)
        ->select('product_id', DB::raw('SUM(amount_paid) as total_paid'))
        ->groupBy('product_id')
        ->with('product')
        ->paginate(10);

        return response()->json($installments);
    }
    
    public function addPayment(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'amount_paid' => 'required|min:0.01',
        ]);

        $installment = new Installment();
        $installment->product_id = $request->product_id;
        $installment->customer_id = $request->customer_id;
        $installment->amount_paid = $request->amount_paid;
        $installment->user_id = Auth::user()->id;
        $installment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment added successfully',
            'data' => $installment
        ]);
    }
}
