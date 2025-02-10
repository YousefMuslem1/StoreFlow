<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\Caliber;
use App\Models\Product;
use App\Models\Quantity;
use Illuminate\Http\Request;
use App\Enums\UserRoleStatus;
use App\Models\WeightQuantity;
use Illuminate\Validation\Rule;
use App\Enums\QuantitySelledTypes;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use App\Http\Middleware\IsWatcherMiddleware;
use App\Enums\ProductStatus as EnumsProductStatus;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $totalWeightOnSelledDate = 0;
        $typeFilter = request('type');
        $caliberFilter = request('caliber'); 
        $statusFilter = request('status');
        $dateFilter = $request->input('filter_date');
        $products = Product::with('type')->with('user')->with('caliber')->with('quantity.type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        // Apply filters based on request parameters
        if ($typeFilter && $typeFilter != 0) {
            $products->where('type_id', $typeFilter);
        }

        if ($caliberFilter && $caliberFilter != 0) {
            $products->where('caliber_id', $caliberFilter);
        }

        if ($statusFilter && $statusFilter != 0) {
            $products->where('status', $statusFilter);
        }
        if ($startDate && !$endDate) {
            $products->where(function ($query) use ($startDate) {
                $query->orWhereDate('selled_date', '=', $startDate);
            });
            // the total weight of selled products
            $totalWeightOnSelledDate = $products->whereDate('selled_date', '=', $startDate)->sum('weight');
        } else if ($startDate && $endDate) {
            $products->whereDate('selled_date', '>=', $startDate)
                ->whereDate('selled_date', '<=', $endDate);
            // Calculate the total weight of sold products within the date range
            $totalWeightOnSelledDate = $products->sum('weight');
        }

        // Continue with other filters or sorting as needed

        $products = $products->latest()->paginate(15);
        $types = Type::all();
        $calibers = Caliber::all();
        // Apply filters based on request parameters

        // dd($products);
        $header = Lang::get('products.header');
        return view('products.index', compact('products', 'header', 'types', 'calibers', 'totalWeightOnSelledDate'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {
            // Generate the desired number
            // $short_ident = str_pad($lastProductId, 5, '0', STR_PAD_LEFT);
            $lastProductIdProducts = Product::max('short_ident');
            $lastProductIdQuantity = Quantity::max('short_ident');
            $short_ident = max($lastProductIdProducts, $lastProductIdQuantity) + 1;

            // Calculate total weight for all available products
            $totalWeight = Product::where('status', '=', EnumsProductStatus::AVAILABLE)->get();
            $totalWeightProducts = $totalWeight->sum('weight');
            $lastEnteredWeight = $totalWeight->pluck('weight')->last();

            // Ensure it's a 5-digit number
            // $generatedNumber = substr($short_ident, -5);
            $types = Type::where('is_quantity', false)->get();

            $quantites = Quantity::with('type')->get();
            $calibers = Caliber::all();
            $header = Lang::get('products.add_product');
            return view('products.create', compact('header', 'types', 'calibers', 'short_ident', 'totalWeightProducts', 'lastEnteredWeight', 'quantites'));
        } else return abort(403);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {

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
                // 'price' => 'required'
            ], [
                'short_ident.unique' => 'هذا الرمز مستخدم بالفعل'
            ]);
            if ($request->quantity_check_box) {
                $request->validate([
                    'quantity' => 'required'
                ], [
                    'quantity.required' => 'يرجى تحديد الصنف المراد سحب الوزن منه'
                ]);
            }
            try {
                DB::transaction(function () use ($request) {
                    //insert in products table as selled (status)
                    $product =  Product::create([
                        'weight' => $request->weight,
                        'description' => $request->desc,
                        'name' => $request->name,
                        'measurement' => $request->measur,
                        'ounce_price' => $request->ounce_price,
                        'price' => $request->price,
                        'ident' => $request->ident,
                        'short_ident' => $request->short_ident,
                        'caliber_id' => $request->caliber,
                        'type_id' => $request->type
                    ]);
                    //insert in weight_quantities table as selled
                    if ($request->quantity_check_box) {
                        WeightQuantity::create([
                            'quantity_id' => $request->quantity,
                            'product_id' => $product->id,
                            'weight' => -$product->weight,
                            'status' => QuantitySelledTypes::FROMOLDTONEW,
                            'notice' => 'تم التحويل من صنف كمية الى جديد',
                            'user_id' => Auth::user()->id,
                        ]);
                    }
                });
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
            return redirect()->route('products.create')->with('message', Lang::get('alert.success_insert'));
        } else return abort(403);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $header = Lang::get('products.product_details');
        $product = $product->load('type', 'caliber', 'user');
        return view('products.view', compact('product', 'header'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // dd($product);
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {

            $calibers = Caliber::all();
            $types = Type::where('is_quantity', false)->get();

            return view('products.edit', compact('product', 'calibers', 'types'));
        } else return abort(403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {
            $request->validate([
                'ident' => 'required',
                'caliber' => 'required',
                'type' => 'required',
                'weight' => 'required',
                'ident' => [
                    'required',
                    Rule::unique('products')->ignore($product->id),
                    Rule::unique('quantities')->ignore($product->id),
                    'regex:/^[0-9a-fA-F]+$/',
                    'max:24',
                    'min:24'
                ],
                'status' => 'required|integer',
                'short_ident' => [
                    'required',
                    Rule::unique('products')->ignore($product->id),
                    Rule::unique('quantities')->ignore($product->id),
                ],
            ], [
                'short_ident.unique' => 'هذا الرمز مستخدم من قبل'
            ]);
            $product->update([
                'weight' => $request->weight,
                'description' => $request->desc,
                'name' => $request->name,
                'measurement' => $request->measur,
                'status' => $request->status,
                'ident' => $request->ident,
                'ounce_price' => $request->ounce_price,
                'selled_price' => $request->selled_price,
                'short_ident' => $request->short_ident,
                'status' => $request->status,
                'caliber_id' => $request->caliber,
                'type_id' => $request->type,
                'selled_date' => $request->selled_date,
            ]);
            $product->save();
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_updated'), Lang::get('alert.successfully'));
            return redirect()->back()->with('message', Lang::get('alert.success_updated'));
        } else return abort(403);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {
            $product->delete();
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_deleted'), Lang::get('alert.successfully'));
            return redirect()->back();
        } else return abort(403);
    }

    public function sell(Product $product, Request $request)
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {
            $caliber = Caliber::where('id', $product->caliber_id)->first();
            $product->selled_date =  Carbon::now();
            $product->selled_price = $request->new_price;
            $product->user_id = Auth::user()->id;
            $product->status = EnumsProductStatus::SOLD;
            $product->caliber_selled_price = $caliber->caliber_price;
            $product->save();
            return back();
        } else return abort(403);
    }

    public function refund(Product $product, Request $request)
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {
            $header = 'استرجاع منتج';
            $new_ident = null;
            $lastProductIdProducts = Product::max('short_ident');
            $lastProductIdQuantity = Quantity::max('short_ident');
            $short_ident = max($lastProductIdProducts, $lastProductIdQuantity) + 1;
            if ($request->method() == Request::METHOD_POST) {
                $request->validate([
                    'ident' => 'required|unique:products,ident'
                ]);
                $product->status = EnumsProductStatus::AVAILABLE;
                $product->ident = $request->ident;
                $product->short_ident = $request->short_ident;
                $product->selled_date = null;
                $product->selled_price = null;
                $product->user_id = null;
                $product->description = null;
                $product->caliber_selled_price = null;
                $product->save();
                $new_ident = $product->ident;
                flash()
                    ->translate(session()->get('locale'))
                    ->addSuccess(Lang::get('alert.success_updated'), Lang::get('alert.successfully'));
            } else {
                $product->with('type')->with('caliber');
            }

            return view('products.refund', compact('product', 'header',  'new_ident', 'short_ident'));
        } else return abort(403);
    }

    public function productSearch(Request $request)
    {
        $searchValue = $request->searchValue;
        $products = Product::with('caliber', 'type', 'user')->where('ident', $searchValue)
            ->orWhere('short_ident', $searchValue)
            ->orWhere('weight', $searchValue)
            ->get();
        $html = View::make('products.search_result', compact('products'))->render();
        return response()->json([
            'html' => $html,
        ]);
    }

    public function reset(Request $request)
    {
        $product = Product::findOrFail($request->product);
        $product->status = EnumsProductStatus::AVAILABLE;
        $product->selled_date = null;
        $product->selled_price = null;
        $product->user_id = null;
        $product->description = null;
        $product->caliber_selled_price = null;
        $product->save();
        flash()
            ->translate(session()->get('locale'))
            ->addSuccess(Lang::get('alert.success_updated'), Lang::get('alert.successfully'));
        return redirect()->back();
    }

    // public function CheckProductExist(Request $request)
    // {
    //     $product = Product::where('ident', $request->value)->first();

    //     if ($product) {
    //         return response()->json('exist', 404);
    //     } else {
    //         return response()->json('not_exist', 200);
    //     }
    // }


}
