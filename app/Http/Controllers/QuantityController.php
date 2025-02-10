<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Type;
use App\Models\Caliber;
use App\Models\Product;
use App\Models\Quantity;
use Illuminate\Http\Request;
use App\Models\WeightQuantity;
use Illuminate\Validation\Rule;
use App\Enums\QuantitySelledTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use function PHPUnit\Framework\isNull;
use Illuminate\Support\Facades\Redirect;
use PhpOffice\PhpSpreadsheet\Reader\Xls\RC4;
use App\Enums\ProductStatus as EnumsProductStatus;

class QuantityController extends Controller
{
    public function __construct()
    {
        $this->middleware('isWatcher')->only(['create', 'addToQuantity', 'addToExistQuantity', 'sellQuantity']);
        // or
        // $this->middleware('your-middleware')->except(['specificFunction3', 'specificFunction4']);
    }
    //get all quantities
    public function index()
    {
        $header = 'إدارة الكميّات';

        // Get quantities with their weightQuantities sum
        $quantitiesQuery = Quantity::with('type', 'caliber')
            ->withSum('weightQuantities', 'weight');

        // Filter by total weight greater than 0.05
        // $quantitiesQuery->having('weight_quantities_sum_weight', '>', 0.05);

        // Get all quantities (without pagination)
        $allQuantities = $quantitiesQuery->orderBy('weight_quantities_sum_weight', 'desc')->get();


        // Paginate the quantities
        $quantities = $quantitiesQuery->paginate(10);

        return view('quantities.index', compact('header', 'quantities', 'allQuantities'));
    }

    // create a new quantity page
    public function create()
    {
        $header = 'إدارة الكميات| إضافة كمية جديدة ';
        $types = Type::where('is_quantity', true)
            ->whereNotIn('id', function ($query) {
                $query->select('type_id')
                    ->from('quantities'); // Replace 'quantities' with your actual table name if it's different
            })
            ->get();
        $calibers = Caliber::all();
        $lastProductIdProducts = Product::max('short_ident');
        $lastProductIdQuantity = Quantity::max('short_ident');
        $short_ident = max($lastProductIdProducts, $lastProductIdQuantity) + 1;
        return view('quantities.create', compact('header', 'types', 'calibers', 'short_ident'));
    }

    // add new quantity
    public function store(Request $request)
    {
        $request->validate([
            'caliber' => 'required',
            'type' => 'required',
            'weight' => 'required',
            'ident' => [
                'required',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('ident', $request->ident);
                }),
                Rule::unique('quantities')->where(function ($query) use ($request) {
                    return $query->where('ident', $request->ident);
                }),
                'regex:/^[0-9a-fA-F]+$/',
                'max:24',
                'min:24'
            ],
            'short_ident' => [
                'required',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('short_ident', $request->short_ident);
                }),
                Rule::unique('quantities')->where(function ($query) use ($request) {
                    return $query->where('short_ident', $request->short_ident);
                }),
            ]
        ], [
            'short_ident.unique' => 'هذا الرمز مستخدم بالفعل'
        ]);
        try {

            DB::transaction(function () use ($request) {
                $quantity = Quantity::create([
                    'ident' => $request->ident,
                    'short_ident' => $request->short_ident,
                    'caliber_id' => $request->caliber,
                    'type_id' => $request->type,
                    'user_id' => Auth::user()->id,
                ]);

                $weight = WeightQuantity::create([
                    'quantity_id' => $quantity->id,
                    'status' => QuantitySelledTypes::NEWQUANTITY,
                    'weight' => $request->weight,
                    'user_id' => Auth::user()->id,
                ]);
            });
        } catch (\Exception $e) {
            // An error occurred, the transaction is rolled back.
            // Handle the exception or log the error.
            flash()
                ->translate(session()->get('locale'))
                ->addError(Lang::get('alert.error_issue'), Lang::get('alert.error'));
            return redirect()->back()->with('error_message', Lang::get('alert.error_issue'));
        }

        flash()
            ->translate(session()->get('locale'))
            ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
        return redirect()->back()->with('message', Lang::get('alert.success_insert'));
    }

    // add new quantity to an existing quantity (Quantities Table)
    public function addToQuantity()
    {
        $header = 'إدارة الكميات|  إضافة كمية جديدة لكميّة موجودة مسبقاً ';
        $types = Type::where('is_quantity', true)->get();
        return view('quantities.add_to_quantity_create', compact('header', 'types'));
    }

    // update existing quantity (weight_quantities Table)
    public function addToExistQuantity(Request $request)
    {
        $status = $request->status;
        $short_ident = $request->quantity_short_ident;
        $weight = $request->weight;
        $ounce_price = $request->ounce_price;
        $price = $request->price;
        $notice = $request->notice;
        $product_short_ident = $request->short_ident;
        // $selled_price = $request->selled_price;
        $quantity = Quantity::with('type')->withSum('weightQuantities', 'weight')->where('short_ident', $short_ident)->first();
        if ($quantity) {
            if ($status == 4) {
                $result = $this->addOutsideQuantity($quantity, $weight, $notice);
                $new_weight = $quantity->weight_quantities_sum_weight + $weight;
                return response()->json(['message' => 'تم اضافة الوزن ' . $weight . ' الى الصنف ' . $quantity->type->name . ' الوزن الجديد ' . $new_weight . ' g', 'weight' => $new_weight], 200);
            } elseif ($status == 5) {
                $product = Product::with('caliber')->with('type')->where('short_ident', $product_short_ident)->first();
                $productOldWeight = $product->weight;
                //check if the weight is greater than the product weight
                $new_product_weight = $product->weight - $weight;
                if ($new_product_weight <= 0) {
                    return response()->json(['message' => 'الوزن المدخل اكبر من وزن المنتج '], 422);
                } else {
                    $result = $this->addLocalQuantity($quantity, $product, $weight, $notice, $new_product_weight);

                    $new_weight = $quantity->weight_quantities_sum_weight + $weight;


                    return response()->json(['message' => 'تم تقصير المنتج من الصنف ' . $product->type->name . ' الوزن الذي تم تقصيره هو ' . $weight . ' g' . ' الوزن القديم للمنتج قبل التعديل هو ' . $productOldWeight . ' الوزن الجديد للمنتج هو' . $new_product_weight . ' g', 'weight' => $new_weight], 200);
                }
            } elseif ($status == 6) {
                $product = Product::where('short_ident', $product_short_ident)->where('status', '!=', EnumsProductStatus::DAMAGED)->first();
                if ($product) {
                    $result = $this->damagedProduct($quantity, $product);
                    $new_weight = $quantity->weight_quantities_sum_weight + $product->weight;
                    return response()->json(['message' => 'تم اتلاف المنتج ذات الرقم' . $product->short_ident . 'وإضافته الى الصنف ' . $quantity->type->name . ' الوزن الجديد للصنف هو ' . $new_weight, 'weight' => $new_weight], 200);
                } else {
                    return response()->json(['message' => 'لم يتم العثور على المنتج'], 404);
                }
            } elseif ($status == 9) {
                $result = $this->buyQuantity($quantity, $weight, $price, $notice, $ounce_price);
                $new_weight = $quantity->weight_quantities_sum_weight + $weight;
                return response()->json(['message' => 'تم اضافة الوزن ' . $weight . ' الى الصنف ' . $quantity->type->name . ' الوزن الجديد ' . $new_weight . ' g', 'weight' => $new_weight], 200);
            }
        } else {
            return response()->json(['message' => 'المنتج غير متوفر أو أنّ الوزن المدخل أكبر من الوزن المتوفر  '], 422);
        }
    }

    // get product information
    public function getProductInfo(Request $request)
    {
        $product = Product::with('type')->with('caliber')->where('short_ident', $request->product)->where('status', '!=', EnumsProductStatus::DAMAGED)->first();

        return response()->json(['product' => $product]);
    }

    public function weightQunatityDetails(Request $request)
    {
        $weightQuantity = WeightQuantity::with('quantity')->with('product')->with('user')->where('id', $request->quantity)->first();
        $header = 'تفاصيل اضافة الى الكمية ' . $weightQuantity->quantity->type->name;
        return view('quantities.weight_quantity_details', compact('weightQuantity', 'header'));
    }

    public function sellQuantity(Request $request)
    {
        $status = $request->status;
        $short_ident = $request->short_ident;
        $weight = $request->editWeight;
        $price = $request->editselledPrice;
        $notice = $request->editNotice;

        // get quantity depending on short_ident
        $quantity = Quantity::with('type')->with('caliber')->with('weightQuantities')->where('short_ident', $short_ident)->first();
        // the sum of wight to this quantity
        $weightsAvailble = $quantity->weightQuantities->sum('weight');
        $new_weight = $weightsAvailble - $weight;
        $typeName = $quantity->type->name;
        //check if weightsAvailble more than the incoming weight and sell quantity
        if ($quantity && $weight > 0 && !is_null($weightsAvailble)  && $weightsAvailble >= $weight) { //259

            if ($status == 1) {
                $selledResult = $this->sellOddQuantity($quantity, $weight, $price, $notice);
                if ($selledResult) {
                    return response()->json(
                        ['message' =>  ' تم بيع الوزن g' . $weight  . ' من الصنف  ' . $typeName . ' ->الوزن الجديد للصنف : ' . $new_weight . ' g', 'oldQuality' => $weightsAvailble]
                    );
                } else {
                    return response()->json(['message' => 'فشلت العملية!']);
                }
            } // end status 1
            elseif ($status == 2) { //merged product status 2
                $product = Product::with('type')->where('short_ident', $request->productShortIdent)->first();
                $productOldWeight = $product->weight;
                $selledResult = $this->mergerdToProduct($quantity, $weight, $price, $notice, $product);
                if ($selledResult) {
                    $newWeight = $product->weight;
                    return response()->json(
                        ['message' =>  ' تم اضافة الوزن  g' . $weight . 'الى المنتج رقم   :  ' . $product->short_ident .  ' من الصنف :' . $product->type->name . ' الوزن القديم: g' . $productOldWeight . ' الوزن الجديد: g' . $newWeight]
                    );
                } else {
                    return response()->json(['message' => 'فشلت العملية!']);
                }
            } // end status 2
            elseif ($status == 3) {
                $selledResult = $this->recycledQuantity($quantity, $weight, $notice);
                if ($selledResult) {
                    return response()->json(['message' => 'تم انقاص الوزن   ' . $weight . ' g من الصنف' . $typeName . ' الوزن الجديد هو' . $new_weight], 200);
                } else {
                    return response()->json(['message' => 'فشلت العملية!']);
                }
            }
        } else {
            return response()->json(['message' => 'المنتج غير متوفر أو أنّ الوزن المدخل أكبر من الوزن المتوفر  '], 422);
        }
    }

    public function transfareQuantity(Request $request)
    {

        if ($request->short_ident == $request->toquantity) {
            return response()->json(['message' => 'لايمكن التحويل ضمن الكمية نفسها'], 422);
        } else {
            try {
                DB::transaction(function () use ($request) {
                    $fromQuantity = Quantity::withSum('weightQuantities', 'weight')->where('short_ident', $request->short_ident)->first();
                    $toQunatity = Quantity::where('short_ident', $request->toquantity)->first();
                    if ($request->TransfareWeight <= $fromQuantity->weight_quantities_sum_weight) {
                        // from quanity
                        WeightQuantity::create([
                            'weight' => $request->TransfareWeight * -1,
                            'quantity_id' => $fromQuantity->id,
                            'user_id' => Auth::user()->id,
                            'status' => QuantitySelledTypes::TRANSFERED,
                            'notice' => $request->TransfareNotice . 'تم تحويل وزن ' . $request->TransfareWeight . ' الى الصنف' . $toQunatity->short_ident,

                        ]);
                        // to quantity
                        WeightQuantity::create([
                            'weight' => $request->TransfareWeight,
                            'quantity_id' => $toQunatity->id,
                            'user_id' => Auth::user()->id,
                            'notice' => $request->TransfareNotice . 'تم تحويل وزن ' . $request->TransfareWeight . ' من الصنف' . $fromQuantity->short_ident,
                            'status' => QuantitySelledTypes::TRANSFERED,

                        ]);
                    } else {
                        throw new \Exception('الوزن المراد تحويله أكبر من الحجم المتوفر ' . $fromQuantity->weight_quantities_sum_weight);
                    }
                    flash()
                        ->translate(session()->get('locale'))
                        ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
                    return redirect()->route('quantities.index');
                });
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        }
    }

    public function editQuantity(WeightQuantity $quantity)
    {
        $header = 'تعديل وزن من كمية';
        return view('quantities.editWeightQuantity', compact('quantity', 'header'));
    }

    public function storeEditQuantity(Request $request)
    {
        $quantity = WeightQuantity::where('id', $request->quantity)->first();
        try {
            DB::transaction(function () use ($request, $quantity) {
                $quantity->update([
                    'created_at' => $request->created_at,
                    'weight' => $request->weight,
                ]);
                $quantity->save();
                if ($quantity->status == EnumsProductStatus::SOLD) {
                    $product = Product::where('id', $quantity->product_id)->update([
                        'weight' => $request->weight * -1,
                        'selled_date' => $request->created_at,
                    ]);
                }
            });
        } catch (\Exception $e) {
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.error_issue'), Lang::get('alert.error'));
            return redirect()->back();
        }

        flash()
            ->translate(session()->get('locale'))
            ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
        return redirect()->route('quantities.quantity_details', $quantity->quantity_id);
    }

    public function destroy(Request $request)
    {



        try {
            // Find the cost by ID
            $quantity = WeightQuantity::findOrFail($request->quantity);


            if ($quantity->status == QuantitySelledTypes::BUYQUANTITY || $quantity->status == QuantitySelledTypes::TRANSFERED) {
                $quantity->delete();
            }


            // Redirect back with success message
            return Redirect::back()->with('success', 'تم حذف العنصر بنجاح');
        } catch (\Exception $e) {
            // Log the error and redirect back with an error message
            Log::error('Error deleting cost: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Error deleting cost. Please try again.');
        }


        return redirect()->route('quantities.index');
    }

    // sell a specific quantity --- first status => 1, 
    //insert the quantity to Products table as selled and in same time to weight_qa+uantites table in negative price (status => 1 ) 
    protected function sellOddQuantity($quantity, $weight, $price, $notice)
    {
        try {
            DB::transaction(function () use ($quantity, $weight, $price, $notice) {
                //insert in products table as selled (status)
                $product = Product::create([
                    'weight' => $weight,
                    'selled_price' => $price,
                    'status' => EnumsProductStatus::SOLD,
                    'description' => $notice,
                    'type_id' => $quantity->type_id,
                    'caliber_id' => $quantity->caliber_id,
                    'caliber_selled_price' => $quantity->caliber->caliber_price,
                    'selled_date' => Carbon::now(),
                    'selled_price' => $price,
                    'user_id' => Auth::user()->id,
                ]);

                //insert in weight_quantities table as selled
                WeightQuantity::create([
                    'quantity_id' => $quantity->id,
                    'product_id' => $product->id,
                    'weight' => -$weight,
                    'status' => QuantitySelledTypes::SELLEDODD,
                    'notice' => $notice,
                    'user_id' => Auth::user()->id,
                ]);
            });
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    // status 2 merged with another product 
    protected function mergerdToProduct($quantity, $weight, $price, $notice, $product)
    {
        // insert the weight to weight_quantites table as a negativ number
        if ($product) {
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
                    // $product->selled_price = $price;
                    // $product->user_id =  Auth::user()->id;
                    // $product->selled_date = Carbon::now();
                    $product->status = EnumsProductStatus::AVAILABLE;
                    $product->save();
                });
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    // status 3 recycled
    protected function recycledQuantity($quantity, $weight, $notice)
    {
        WeightQuantity::create([
            'quantity_id' => $quantity->id,
            'weight' => -$weight,
            'status' => QuantitySelledTypes::RECYCLED,
            'notice' => $notice,
            'user_id' => Auth::user()->id,
        ]);

        return true;
    }

    // status 4 add new quantity to existing quantity which brought from outside status 4
    protected function addOutsideQuantity($quantity, $weight, $notice)
    {
        WeightQuantity::create([
            'quantity_id' => $quantity->id,
            'weight' => $weight,
            'status' => QuantitySelledTypes::NEWQUANTITY,
            'notice' => $notice,
            'user_id' => Auth::user()->id,
        ]);
        return true;
    }

    // status 5 cut weight from a product and add the remaining weight to a quantity
    protected function addLocalQuantity($quantity, $product, $weight, $notice, $new_product_weight)
    {
        try {
            DB::transaction(function () use ($quantity, $product, $weight, $notice, $new_product_weight) {
                // Edit the weight of the product
                $old_product_weight = $product->weight;
                $product->weight = $new_product_weight;
                $product->status = EnumsProductStatus::AVAILABLE;
                $product->description = ($product->description ?? '--------')
                    . ' تم تقصير المنتج من الصنف ' . $product->type->name
                    . ' الوزن الذي تم تقصيره هو ' . $weight . ' g'
                    . ' الوزن القديم للمنتج قبل التعديل هو ' . $old_product_weight
                    . ' الوزن الجديد للمنتج هو ' . $product->weight . ' g';
                $product->save();

                // Insert the remaining weight to WeightQuantities table
                WeightQuantity::create([
                    'quantity_id' => $quantity->id,
                    'product_id' => $product->id,
                    'weight' => $weight,
                    'status' => QuantitySelledTypes::LOCALQUANTITY,
                    'user_id' => Auth::id(),
                    'notice' => $notice
                        . ' تم تقصير المنتج من الصنف ' . $product->type->name
                        . ' الوزن الذي تم تقصيره هو ' . $weight . ' g'
                        . ' الوزن القديم للمنتج قبل التعديل هو ' . $old_product_weight
                        . ' الوزن الجديد للمنتج هو ' . $product->weight . ' g'
                ]);

                return true;
            });
        } catch (\Exception $e) {
            return false;
        }
    }


    protected function damagedProduct($quantity, $product)
    {
        try {
            DB::transaction(function () use ($quantity, $product) {
                $product->update([
                    'status' => EnumsProductStatus::DAMAGED,
                    'description' => $product->description ? $product->description . ' --------معلومات النظام ----------  ' .  'تم اضافة الوزن ' . $product->weight . ' g' . ' للمنتج ' . $product->short_ident . ' الى الصنف ' . $quantity->type->name . ' لانه تالف'
                        : '' . ' --------معلومات النظام ----------  ' .  'تم اضافة الوزن ' . $product->weight . ' g' . ' للمنتج ' . $product->short_ident . ' الى الصنف ' . $quantity->type->name . ' لانه تالف'
                ]);

                WeightQuantity::create([
                    'quantity_id' => $quantity->id,
                    'product_id' => $product->id,
                    'ounce_price' => $product->ounce_price,
                    'weight' => $product->weight,
                    'status' => QuantitySelledTypes::DAMAGEDPRODUCTTOQUANTITY,
                    'user_id' => Auth::user()->id,
                    'notice' => 'تم اضافة الوزن ' . $product->weight . ' g' . ' للمنتج ' . $product->short_ident . ' الى الصنف ' . $quantity->type->name . ' لانه تالف'
                ]);

                return true;
            });
        } catch (\Exception $e) {
            return false;
        }
    }

    // quantity details
    public function quantityDetails(Request $request)
    {
        $quantity = $request->quantity;
        $from = $request->from;
        $to = $request->to;
        // Build the query for WeightQuantity
        $query = WeightQuantity::with('quantity', 'user', 'product')
            ->where('quantity_id', $quantity)
            ->latest();

        // Apply the date filter if provided
        if (!empty($request->from) && !empty($request->to)) {
            $fromDate = Carbon::parse($request->from)->startOfDay();
            $toDate = Carbon::parse($request->to)->endOfDay();
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        } elseif (!empty($request->from)) {
            $fromDate = Carbon::parse($request->from)->startOfDay();
            $query->whereDate('created_at', '>=', $fromDate);
        } elseif (!empty($request->to)) {
            $toDate = Carbon::parse($request->to)->endOfDay();
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Paginate the results
        $quantities = $query->paginate(10);

        // Check if there are any quantities found
        if ($quantities->isNotEmpty()) {
            // Calculate the total weight based on the filtered records
            $total_weight = $query->sum('weight');

            // Fetch the main quantity details with its type (no need to use withSum here)
            $mainQuantity = Quantity::with('type')
                ->where('id', $quantity)
                ->first();

            // Set the header
            $header = 'تفاصيل كمية لصنف ' . $mainQuantity->type->name;
        } else {
            // If no quantities found, abort with a 404 error
            return redirect()->back()->withInput()->withErrors(['quantity' => 'No quantities found for the selected date range.']);
        }

        // Return the view with the data
        return view('quantities.quantity_details', compact('header', 'quantities', 'total_weight', 'quantity', 'from', 'to')); // This ensures old input is preserved
    }

    // buy quantity

    public function buyQuantity($quantity, $weight, $price, $notice, $ounce_price)
    {
        WeightQuantity::create([
            'quantity_id' => $quantity->id,
            'weight' => $weight,
            'ounce_price' => $ounce_price,
            'status' => QuantitySelledTypes::BUYQUANTITY,
            'price' => $price,
            'notice' => $notice,
            'user_id' => Auth::user()->id,
        ]);
        return true;
    }
}
