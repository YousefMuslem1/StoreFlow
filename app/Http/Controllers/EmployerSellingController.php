<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use Telegram\Bot\Api;
use App\Models\Caliber;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Quantity;
use App\Models\Installment;
use App\Enums\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\SendTelegramMessageJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Enums\ProductStatus as EnumsProductStatus;

class EmployerSellingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('employer.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // sell the product
    public function store(Request $request)
    {
        $paymentWay = $request->paymentdWay;
        $payedMoney = $request->payedMoney;
        $newCusotmerRadio = $request->newCusotmerRadio;
        $price = $request->price;
        $notice = $request->notice;
        $request->validate([
            'price' => 'required'
        ]);

        $product = Product::where('ident', $request->ident)->first();
        $caliber = Caliber::where('id', $product->caliber_id)->first();
        if ($paymentWay == 1) { // full payment
            if ($product->status != ProductStatus::SOLD) {
                $updated_product = $this->sellProduct($product, $price, $notice, $caliber->caliber_price);

                $message = "----------نوع المبيع : مفرد -------------"
                    . "\nرمز المنتج: " . $updated_product->short_ident .
                    "\nسعر المبيع: " . $updated_product->selled_price . " €"
                    . "\nالوزن: " . $updated_product->weight . ' g'
                    . "\n سعر الغرام: " . number_format($updated_product->selled_price / $updated_product->weight, 2) . " €"
                    . "\n الصنف: " . $updated_product->type->name
                    . "\n العيار: " . $updated_product->caliber->full_name
                    . "\n البائع:" . $updated_product->user->name
                    . "\n ملاحظات: " . $updated_product->description ?? 'لايوجد';
                    SendTelegramMessageJob::dispatch($message);

                flash()
                    ->translate(session()->get('locale'))
                    ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
                return redirect()->route('sell.index');
            } else {
                flash()
                    ->translate(session()->get('locale'))
                    ->addError(Lang::get('alert.selled'), Lang::get('alert.error'));
                return view('employer.index');
            }
        } else { // installment payment

            $request->validate([
                'payedMoney' => 'required',
            ]);

            if ($request->newCusotmerRadio == 1) { // new customer
                $request->validate([
                    'customerName' => 'required|unique:customers,name',
                    'customerPhone' => 'required|unique:customers,phone',
                ]);
                // dd($request);

                try {
                    DB::transaction(function () use ($product, $request, $price, $notice, $caliber, $payedMoney) {
                        $customer = Customer::create([
                            'name' => $request->customerName,
                            'phone' => $request->customerPhone,
                        ]);

                        $product_selled =  $this->sellProduct($product, $price, $notice, $caliber->caliber_price);

                        $installment_product = Installment::create([
                            'product_id' => $product_selled->id,
                            'customer_id' => $customer->id,
                            'amount_paid' => $payedMoney,
                            'user_id' => Auth::user()->id,
                        ]);

                        $message = "----------نوع المبيع : تقسيط -------------"
                            . "\nرمز المنتج: " . $product_selled->short_ident .
                            "\nسعر المبيع: " . $product_selled->selled_price . " €"
                            . "\n العميل :" . $installment_product->customer->name
                            . "\n مدفوع : " . $installment_product->amount_paid . " €"
                            . "\n باقي : " . $product_selled->selled_price -  $installment_product->amount_paid . " €"
                            . "\nالوزن: " . $product_selled->weight . ' g'
                            . "\n سعر الغرام: " . number_format($product_selled->selled_price / $product_selled->weight, 2) . " €"
                            . "\n الصنف: " . $product_selled->type->name
                            . "\n العيار: " . $product_selled->caliber->full_name
                            . "\n البائع:" . $product_selled->user->name
                            . "\n ملاحظات: " . $product_selled->description ?? 'لايوجد';
                            SendTelegramMessageJob::dispatch($message);
                    });
                } catch (\Exception $e) {
                    return $e->getMessage();
                    flash()
                        ->translate(session()->get('locale'))
                        ->addError(Lang::get('alert.selled'), Lang::get('alert.error'));
                    return redirect()->back();
                }
                flash()
                    ->translate(session()->get('locale'))
                    ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
                return redirect()->route('sell.index');
            } else { //existing customer
                $customer = Customer::where('name', $request->customerName)->first();
                if ($customer) {
                    try {
                        DB::transaction(function () use ($product, $price, $notice, $caliber, $customer, $payedMoney) {
                            $product_selled =  $this->sellProduct($product, $price, $notice, $caliber->caliber_price);
                            $installment_product = Installment::create([
                                'product_id' => $product_selled->id,
                                'customer_id' => $customer->id,
                                'amount_paid' => $payedMoney,
                                'user_id' => Auth::user()->id,
                            ]);
                            $message = "----------نوع المبيع : تقسيط -------------"
                                . "\nرمز المنتج: " . $product_selled->short_ident .
                                "\nسعر المبيع: " . $product_selled->selled_price . " €"
                                . "\n العميل :" . $installment_product->customer->name
                                . "\n مدفوع : " . $installment_product->amount_paid . " €"
                                . "\n باقي : " . $product_selled->selled_price -  $installment_product->amount_paid . " €"
                                . "\nالوزن: " . $product_selled->weight . ' g'
                                . "\n سعر الغرام: " . number_format($product_selled->selled_price / $product_selled->weight, 2) . " €"
                                . "\n الصنف: " . $product_selled->type->name
                                . "\n العيار: " . $product_selled->caliber->full_name
                                . "\n البائع:" . $product_selled->user->name
                                . "\n ملاحظات: " . $product_selled->description ?? 'لايوجد';
                                SendTelegramMessageJob::dispatch($message);
                        });
                    } catch (\Exception $e) {
                    }
                    flash()
                        ->translate(session()->get('locale'))
                        ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
                    return redirect()->route('sell.index');
                } else {
                    flash()
                        ->translate(session()->get('locale'))
                        ->addError('إسم العميل غير متوفر', Lang::get('alert.error'));
                }
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $product = Product::where('status', EnumsProductStatus::AVAILABLE)->where('short_ident', $request->identifier)->orWhere('ident', $request->identifier)->with('caliber')->with('type')->first();
        if (!$product) {
            // Product not found, return a response indicating this
            return response()->json($product, 404);
        }
        return response($product, 200);
    }
    public function getProduct(Request $request)
    {
        $product = Product::where('id', $request->product)
            ->with(['caliber', 'type', 'order.customer', 'user'])
            ->first();

        if (!$product) {
            // Product not found, return a response indicating this
            return abort(404);
        }

        // Calculate the sum of amount_paid
        $productOrder = Order::where('product_id', $product->id)->first();
        $amountPaidSum = $productOrder->payments->sum('amount') ?? 0;

        $product->status_name = ProductStatus::getStatus($product->status);

        return view('employer.product_details', compact('product', 'amountPaidSum'));
    }

    public function sellManyProducts(Request $request)
    {
        $totalPrice = $request->totalPrice;
        // Fetch products corresponding to the given identifiers
        $products = Product::whereIn('ident', $request->identifiers)->get();

        // Calculate the total sum of weights
        $totalWeight = $products->sum('weight');

        $user = '';
        $message = "";
        // Calculate price per unit weight
        $pricePerUnitWeight = $totalPrice / $totalWeight;
        // Iterate through each product
        foreach ($products as $product) {
            // Calculate the parice for the product
            $productPrice = $product->weight * $pricePerUnitWeight;

            // Collect short_ident for each product
            $shortIdents[] = $product->short_ident;
            $description = 'تم بيع هذا المنتج مع المنتجات( quantity ):';
            foreach ($products as $prod) {
                if ($product->short_ident != $prod->short_ident)
                    $description .= $prod->short_ident . ' , ';
            }
            // Update the product's selled_price and status
            $product->update([
                'selled_price' => $productPrice,
                'status' => EnumsProductStatus::SOLD,
                'selled_date' => Carbon::now(),
                'user_id' => Auth::user()->id,
                'description' => $description
            ]);
            // Append the details of each product to the message
            $message .= "\nرمز المنتج: " . $product->short_ident .
                "\nسعر المبيع: " . number_format($product->selled_price, 2) . " €" .
                "\nالوزن: " . $product->weight . ' g' .
                "\nسعر الغرام: " . number_format($product->selled_price / $product->weight, 2) . " €" .
                "\nالصنف: " . $product->type->name .
                "\nالعيار: " . $product->caliber->full_name .
                "\nالبائع: " . $product->user->name .
                "\nملاحظات: " . ($product->description ?? 'لايوجد') . "\n-----------------------------\n";
            $user = $product->user->name;
        }

        $message =  "----------نوع المبيع : جملة-------------\n" .
            "\nرموز المنتجات: " . implode(', ', $shortIdents)
            . "\nإجمالي الوزن: " . $totalWeight . " g"
            . "\nإجمالي السعر: " . $totalPrice . " €"
            . "\nمتوسط سعر الغرام: " . number_format($totalPrice / $totalWeight, 2)  . " €"
            . "\n البائع: " . $user
            . "\n ----------------------" . $message;

        sendTelegramMessage($message);

        return response()->json('تم بيع ' . count($products) . ' منتجات' . ' بسعر ' . $totalPrice . ' بنجاح', 200);
    }

    // search for products depending in last 4 characters
    public function search(Request $request)
    {
        if ($request->ident) {
            $product = Product::where('short_ident', $request->ident)->orWhere('ident', $request->ident)
                ->with('caliber')
                ->with('type')
                ->first();
            if (!$product) {
                // Product not found, return a response indicating this
                flash()
                    ->translate(session()->get('locale'))
                    ->addError(Lang::get('alert.notfound'), Lang::get('alert.error'));
                return view('employer.index');
            }
            return view('employer.index', compact('product'));
        }
        return view('employer.index');
    }

    public function sales()
    {
        $products = Product::with('caliber')->with('type')->whereDate('selled_date', Carbon::today())->latest()->get();

        $newProducts = $this->getNewProducts();
        $newQuantities = $this->getNewQuantities();
        return view('employer.sales', compact('products', 'newProducts', 'newQuantities'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function getNewProducts()
    {
        $products = Product::with('caliber')->with('type')->where('ident', '!=', Null)->whereDate('created_at', Carbon::today())->get();
        return $products;
    }
    private function getNewQuantities()
    {
        $quantities = Quantity::with(['weightQuantities' => function ($query) {
            $query->where('weight', '>', 0)
                ->whereDate('created_at', Carbon::today());
        }])->with('caliber')->with('type')->get();
        return $quantities;
    }

    public function cancelSelledProduct(Request $request)
    {

        $product = Product::findOrFail($request->product);

        $message = "----------ملغي-------------"
            . "\nرمز المنتج: " . $product->short_ident .
            "\nسعر المبيع: " . $product->selled_price . " €"
            . "\nالوزن: " . $product->weight . ' g'
            . "\n سعر الغرام: " . number_format($product->selled_price / $product->weight, 2) . " €"
            . "\n الصنف: " . $product->type->name
            . "\n العيار: " . $product->caliber->full_name
            . "\n البائع:" . $product->user->name
            . "\n ملاحظات: " . $product->description ?? 'لايوجد';

        $product->status = EnumsProductStatus::AVAILABLE;
        $product->selled_date = null;
        $product->selled_price = null;
        $product->user_id = null;
        $product->description = null;
        $product->caliber_selled_price = null;
        $product->save();

        SendTelegramMessageJob::dispatch($message);
        flash()
            ->translate(session()->get('locale'))
            ->addSuccess(Lang::get('alert.success_updated'), Lang::get('alert.successfully'));
        return redirect()->back();
    }

    private function sellProduct($product, $price, $notice, $caliber_price)
    {
        $product->selled_price = $price;
        $product->caliber_selled_price = $caliber_price;
        $product->description = $notice;
        $product->selled_date = Carbon::now();
        $product->user_id = auth()->user()->id;
        $product->status = ProductStatus::SOLD;
        $product->save();
        return $product;
    }
}
