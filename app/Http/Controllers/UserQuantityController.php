<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Type;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Quantity;
use App\Models\Installment;
use Illuminate\Http\Request;
use App\Models\WeightQuantity;
use App\Enums\QuantitySelledTypes;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\SendTelegramMessageJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Enums\ProductStatus as EnumsProductStatus;

class UserQuantityController extends Controller
{
    public function quantity()
    {
        $types = Type::where('is_quantity', true)->latest()->get();
        return view('employer.quantity', compact('types'));
    }

    public function sellFromQuantity(Request $request)
    { 
        // dd($request);
        $request->validate([
            'proccess' => 'required',
            'type' => 'required',
        ]);
        $product_short_ident = $request->product_short_ident;
        $type_id = $request->type;
        $paymentWay = $request->paymentdWay;
        $payedMoney = $request->payedMoney;
        $newCusotmerRadio = $request->newCusotmerRadio;
        $proccess = $request->proccess;
        $weight = $request->weight;
        $price = $request->price;
        $notice = $request->notice;
        $quantity = Quantity::with('type')
            ->with('caliber')
            ->with('weightQuantities')
            ->withSum('weightQuantities', 'weight')
            ->where('type_id', $type_id)
            ->first();
        // status 1
        if ($proccess == QuantitySelledTypes::SELLEDODD) {
            if ($weight > $quantity->weight_quantities_sum_weight) {
                return redirect()->back()->with('error_message', 'الوزن غير متوفر!');
            }
            if ($paymentWay == 1) { //full payment
                $result = $this->sellOddQuantity($quantity, $weight, $price, $notice);
                if ($result) {
                    return redirect()->route('sell.quantity')->with('success_message',  ' تم اضافة الوزن ' . $weight . ' من الصنف ' . $quantity->type->name . ' الى المبيعات ' . ' بنجاح!');
                } else {
                    return redirect()->route('sell.quantity')->with('error_message',  'حدث خطأ ما يرجى المحاولة مجدداً');
                }
            } else { //installment
                $request->validate([
                    'payedMoney' => 'required',
                ]);

                if ($request->newCusotmerRadio == 1) { // new customer
                    $request->validate([
                        'customerName' => 'required|unique:customers,name',
                        'customerPhone' => 'required|unique:customers,phone',
                    ]);

                    try {
                        DB::transaction(function () use ($quantity, $request, $weight, $price, $notice, $payedMoney) {
                            $customer = Customer::create([
                                'name' => $request->customerName,
                                'phone' => $request->customerPhone,
                            ]);

                            $product_selled =  $this->sellOddQuantity($quantity, $weight, $price, $notice);
                            $installment_product = Installment::create([
                                'product_id' => $product_selled->id,
                                'customer_id' => $customer->id,
                                'amount_paid' => $payedMoney,
                                'user_id' => Auth::user()->id,
                            ]);
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
                    return redirect()->route('sell.quantity');
                } else { //existing customer
                    $customer = Customer::where('name', $request->customerName)->first();
                    if ($customer) {
                        try {
                            DB::transaction(function () use ($quantity, $request,$customer, $weight, $price, $notice, $payedMoney) {
                                $product_selled =  $this->sellOddQuantity($quantity, $weight, $price, $notice);
                                $installment_product = Installment::create([
                                    'product_id' => $product_selled->id,
                                    'customer_id' => $customer->id,
                                    'amount_paid' => $payedMoney,
                                    'user_id' => Auth::user()->id,
                                ]);
                            });
                        } catch (\Exception $e) {
                        }
                        flash()
                            ->translate(session()->get('locale'))
                            ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
                        return redirect()->route('sell.quantity');
                    } else {
                        flash()
                            ->translate(session()->get('locale'))
                            ->addError('إسم العميل غير متوفر', Lang::get('alert.error'));
                    }
                }
            }
        }
        // status 2
        elseif ($proccess == QuantitySelledTypes::MARGED) {

            $product = Product::with('type')->where('short_ident', $product_short_ident)->orWhere('ident', $product_short_ident)->first();
            $productOldWeight = $product->weight;
            $selledResult = $this->mergerdToProduct($quantity, $weight, $price, $notice, $product);
            if ($selledResult) {
                $newWeight = $product->weight;

                return redirect()->route('sell.quantity')->with('success_message', ' تم اضافة الوزن  g' . $weight . 'الى المنتج رقم   :  ' . $product->short_ident .  ' من الصنف :' . $product->type->name . ' الوزن القديم: g' . $productOldWeight . ' الوزن الجديد: g' . $newWeight);
            } else {
                return redirect()->route('sell.quantity')->with('error_message',  'حدث خطأ ما يرجى المحاولة مجدداً');
            }
        } elseif ($proccess == QuantitySelledTypes::LOCALQUANTITY) {
            $product = Product::with('type')->where('short_ident', $product_short_ident)->orWhere('ident', $product_short_ident)->first();
            $productOldWeight = $product->weight;
            //check if the weight is greater than the product weight
            $new_product_weight = $product->weight - $weight;
            if ($new_product_weight <= 0) {
                return redirect()->route('sell.quantity')->with('error_message',  'لا يمكن تنفيذ العملية ,الوزن المدخل أكبر من وزن المنتج الحالي !');
            } else {
                $result = $this->addLocalQuantity($quantity, $product, $weight, $notice, $new_product_weight, $price);
                // $new_weight = $quantity->weight_quantities_sum_weight + $weight;

                return redirect()->route('sell.quantity')->with('success_message', 'تم تقصير المنتج من الصنف ' . $product->type->name . ' الوزن الذي تم تقصيره هو ' . $weight . ' g' . ' الوزن القديم للمنتج قبل التعديل هو ' . $productOldWeight . ' الوزن الجديد للمنتج هو' . $new_product_weight . ' g');
            }
        } elseif ($proccess == QuantitySelledTypes::BUYQUANTITY) {
            $new_quantity = WeightQuantity::create([
                'quantity_id' => $quantity->id,
                'weight' => $weight,
                'status' => QuantitySelledTypes::BUYQUANTITY,
                'price' => $price,
                'notice' => $notice,
                'user_id' => Auth::user()->id,
            ]);
            $message = "-----------شراء كمية------------"
                . "\nرمز الكمية: " . $quantity->short_ident .
                "\nسعر الشراء: " . $new_quantity->price . " €"
                . "\nالوزن: " . $new_quantity->weight . ' g'
                . "\n سعر الغرام: " . number_format($new_quantity->price / $new_quantity->weight, 2) . " €"
                . "\n الصنف: " . $quantity->type->name
                . "\n العيار: " . $quantity->caliber->full_name
                . "\n المسؤول:" . $new_quantity->user->name
                . "\n ملاحظات: " . $new_quantity->notice ?? 'لايوجد';
                SendTelegramMessageJob::dispatch($message);

            return redirect()->route('sell.quantity')->with('success_message', 'تمت عملية الشراء بنجاح  ' . ' الصنف: ' . $quantity->type->name . ' الوزن : ' . $weight . 'g');
        } else {
            return redirect()->route('sell.quantity')->with('error_message',  'حدث خطأ ما يرجى المحاولة مجدداً');
        }
    }

    // status 1
    protected function sellOddQuantity($quantity, $weight, $price, $notice)
    {
        try {
            DB::transaction(function () use (&$product, $quantity, $weight, $price, $notice) {
                //insert in products table as selled (status)
                $product = Product::create([
                    'weight' => $weight,
                    'selled_price' => $price,
                    'status' => EnumsProductStatus::SOLD,
                    'description' => $notice,
                    'type_id' => $quantity->type_id,
                    'caliber_id' => $quantity->caliber_id,
                    'caliber_selled_price' => $quantity->caliber->caliber_price,
                    'selled_price' => $price ?? $quantity->caliber->caliber_price * $weight,
                    'selled_date' => Carbon::now(),
                    'user_id' => Auth::user()->id,
                ]);

                //insert in weight_quantities table as selled
                WeightQuantity::create([
                    'quantity_id' => $quantity->id,
                    'product_id' => $product->id,
                    'weight' => $weight * -1,
                    'status' => QuantitySelledTypes::SELLEDODD,
                    'notice' => $notice,
                    'user_id' => Auth::user()->id,
                ]);

                $message = "-----نوع المبيع : كمية مبيع مفرد -----"
                . "\nرمز الكمية: " . $quantity->short_ident .
                "\nسعر المبيع: " . $product->selled_price . " €"
                . "\nالوزن: " . $product->weight . ' g'
                . "\n سعر الغرام: " . number_format($product->selled_price / $product->weight, 2) . " €"
                . "\n الصنف: " . $product->type->name
                . "\n العيار: " . $product->caliber->full_name
                . "\n البائع:" . $product->user->name
                . "\n ملاحظات: " . $product->description ?? 'لايوجد';
            sendTelegramMessage($message);

            });
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $product;

    }

    // status 2 merged with another product 
    protected function mergerdToProduct($quantity, $weight, $price, $notice, $product)
    {
        // insert the weight to weight_quantites table as a negativ number
        $productWeight = $product->weight;
        $newWeight = $product->weight + $weight;
        try {
            DB::transaction(function () use ($quantity, $weight, $price, $notice, $product, $newWeight, $productWeight) {
                //insert negative weight to weight_quantities table 
                WeightQuantity::create([
                    'quantity_id' => $quantity->id,
                    'product_id' => $product->id,
                    'weight' => -$weight,
                    'status' => QuantitySelledTypes::MARGED,
                    'notice' => $product->description .  ' ------تعديلات النظام -------- ' . 'تم سحب الوزن: g  ' . $weight . ' من الصنف ' . $quantity->type->name . 'وإضافته الى المنتج ذات الرقم ' . $product->short_ident
                        . ' الوزن القديم للمنتج هو : ' . $product->productWeight . ' -> الوزن الجديد g' . $newWeight,
                    'user_id' => Auth::user()->id,
                ]);
                $product->weight = $newWeight;
                $product->description = $notice . $product->description . ' ------تعديلات النظام -------- ' . 'تم سحب الوزن: g  ' . $weight . ' من الصنف ' . $quantity->type->name . 'وإضافته الى المنتج ذات الرقم ' . $product->short_ident
                    . ' الوزن القديم للمنتج هو : ' . $productWeight . '  الوزن الجديد g' . $newWeight;
                if ($price) {
                    $product->caliber_selled_price = $product->caliber->caliber_price;
                    $product->selled_price = $price;
                    $product->user_id =  Auth::user()->id;
                    $product->selled_date = Carbon::now();
                    $product->status = EnumsProductStatus::SOLD;
                }

                $product->save();
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // status 5 cut weight from a product and add the remaining weight to a quantity
    protected function addLocalQuantity($quantity, $product, $weight, $notice, $new_product_weight, $price)
    {
        try {
            DB::transaction(function () use ($quantity, $product, $weight, $notice, $new_product_weight, $price) {
                //edit the weight of the product
                $old_product_weight = $product->weight;
                $product->weight = $new_product_weight;
                $product->description = $product->description ?? '--------';
                $product->description = $product->description  .   'تم تقصير المنتج من الصنف ' . $product->type->name . ' الوزن الذي تم تقصيره هو ' . $weight . ' g' . ' الوزن القديم للمنتج قبل التعديل هو ' . $old_product_weight . ' الوزن الجديد للمنتج هو' . $product->weight . ' g';
                if ($price) {
                    $product->status = EnumsProductStatus::SOLD;
                    $product->selled_price = $price;
                    $product->selled_date = Carbon::now();
                    $product->user_id = Auth::user()->id;
                    $product->caliber_selled_price = $product->caliber->caliber_price;
                }
                $product->save();

                //insert the remaining weight to WeightQuantites table
                $quantity = WeightQuantity::create([
                    'quantity_id' => $quantity->id,
                    'product_id' => $product->id,
                    'weight' => $weight,
                    'status' => QuantitySelledTypes::LOCALQUANTITY,
                    'user_id' => Auth::user()->id,
                    'notice' => ' ' . 'تم تقصير المنتج من الصنف ' . $product->type->name . ' الوزن الذي تم تقصيره هو ' . $weight . ' g' . ' الوزن القديم للمنتج قبل التعديل هو ' . $old_product_weight . ' الوزن الجديد للمنتج هو' . $product->weight . ' g' . ' ------' . $notice
                ]);

                return  true;
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getDeviceModePage()
    {
        return view('employer.device_mode_page');
    }

    // get product information
    public function getProductInfo(Request $request)
    {
        $product = Product::with('type')->with('caliber')->where('short_ident', $request->product)->orWhere('ident', $request->product)->where('status', '!=', EnumsProductStatus::DAMAGED)->first();

        return response()->json(['product' => $product]);
    }
}
