<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Box;
use App\Models\Cost;
use App\Models\Type;
use App\Models\Order;
use App\Models\Caliber;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quantity;
use App\Enums\OrderStatus;
use App\Models\Installment;

use App\Models\Maintenance;
use Illuminate\Http\Request;
use App\Models\WeightQuantity;
use Illuminate\Validation\Rule;
use App\Enums\MaintenanceStatus;
use App\Enums\QuantitySelledTypes;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Enums\ProductStatus as EnumsProductStatus;

class DataEntryController extends Controller
{
    private $totalSumAfterTransforming = 0;

    public function index()
    {
        // $this->calcOpenedBoxForToday();
        $header = 'الرئيسية';
        $types = Type::all();
        $calibers = Caliber::all();
        $weights = $this->calculateWeights($types, $calibers); // details products
        $sumWeights = $this->calculateSumWeights($types, $calibers);  //public statestics
        $quantitestotalWeights = $this->CalculateQuantitiesWeights();  //quantities
        $detialedQuantites = clone $quantitestotalWeights;  //quantities

        // Get the current date
        $startDateTime = Carbon::today()->startOfDay();

        $selled_products = Product::with(['caliber', 'type', 'user', 'installments', 'order'])
            ->where('selled_date', '>=', $startDateTime)
            ->whereDoesntHave('order')
            ->orderBy('updated_at', 'asc')
            ->get();

        $installments = Installment::with(['product.type', 'customer'])
            ->whereDate('created_at', $startDateTime)
            ->get();

        $totalAmountInstallmentsPaidToday = $installments->sum('amount_paid');

        // Fetch all installments excluding today
        $previousInstallments = Installment::with(['product', 'customer'])
            ->whereDate('created_at', '<', $startDateTime)
            ->get();

        // Calculate total amount paid excluding today's payments
        $productTotalPaidExcludingToday = [];
        foreach ($previousInstallments as $installment) {
            $productId = $installment->product_id;
            if (!isset($productTotalPaidExcludingToday[$productId])) {
                $productTotalPaidExcludingToday[$productId] = 0;
            }
            $productTotalPaidExcludingToday[$productId] += $installment->amount_paid;
        }

        // Calculate total amount paid today
        $productTotalPaidToday = [];
        foreach ($installments as $installment) {
            $productId = $installment->product_id;
            if (!isset($productTotalPaidToday[$productId])) {
                $productTotalPaidToday[$productId] = 0;
            }
            $productTotalPaidToday[$productId] += $installment->amount_paid;
        }

        // Calculate the total amount paid for each product
        $selled_products->each(function ($product) {
            $product->total_amount_paid = $product->installments->sum('amount_paid');
        });

        // Count of products sold today
        $selledTodayCount = $selled_products->count();

        // Sum of weights sold today
        $selledSumTodayWeight = $selled_products->sum('weight');

        // Calculate the total price for sold products today
        $selledTodayPriceTotal = $selled_products->reduce(function ($carry, $product) {
            if ($product->installments->isNotEmpty()) {
                // Sum of installments' amount paid
                return $carry;
            } else {
                // Directly using selled price
                return $carry + $product->selled_price;
            }
        }, 0);

        $bgColors = ['info', 'success', 'warning', 'danger', 'secondary', 'primary'];
        // Chart for daily selling
        $salesData = Product::selectRaw('DATE(selled_date) as date, SUM(selled_price) as total_sales')
            ->whereNotNull('selled_date')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fetch data for weight chart
        $weightData = Product::selectRaw('DATE(selled_date) as date, SUM(weight) as total_weight')
            ->whereNotNull('selled_date')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = $salesData->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('Y-m-d');
        });
        $totalSales = $salesData->pluck('total_sales');
        $totalSelledWeight = $weightData->pluck('total_weight');

        $totalSumAfterTransforming = number_format($this->totalSumAfterTransforming, 3) . ' g';

        // New products quantities
        $newProductsResult = $this->getNewProducts();
        $newProducts = $newProductsResult[0];
        $newProductWeight = $newProducts->sum('weight');

        $newQuantitiesResult = $this->getNewQuantities();
        $newQuantities = $newQuantitiesResult[0];
        $newQuantityWeight = WeightQuantity::where('weight', '>', 0)
            ->whereDate('created_at', Carbon::today())
            ->where(function ($query) {
                $query->where('status', QuantitySelledTypes::BUYQUANTITY)
                    ->orWhere('status', QuantitySelledTypes::NEWQUANTITY);
            })
            ->sum('weight');

        $newboughtedQuantity = $this->boughtQuantity();
        $newBoughtPrice = $newboughtedQuantity->sum('price');
        $totalFineEnteredTodayWeigt = $newProductsResult[1] + $newQuantitiesResult[1];

        // Get today box
        $box = Box::whereDate('created_at', Carbon::today())->first();

        // Costs
        $costs = $this->getCosts();
        $totalCostsValue = $costs->isNotEmpty() ? $costs->first()->total_cost_value : 0;

        // Orders

        $lastPayments = $this->getPaymentsForToday();


        // Maintenance orders
        $maintenances = $this->getTodayMaintenanceOrders();
        $totalMaintenacesValue = $maintenances['totalMaintenanceValue'];
        $allMaintenances = $maintenances['allMaintenances'];

        return view('index', compact(
            'dates',
            'calibers',
            'totalSales',
            'totalSelledWeight',
            'header',
            'weights',
            'sumWeights',
            'types',
            'totalSumAfterTransforming',
            'bgColors',
            'selled_products',
            'selledTodayCount',
            'selledSumTodayWeight',
            'selledTodayPriceTotal',
            'quantitestotalWeights',
            'detialedQuantites',
            'newProducts',
            'newProductWeight',
            'newQuantities',
            'newQuantityWeight',
            'totalFineEnteredTodayWeigt',
            'newboughtedQuantity',
            'newBoughtPrice',
            'box',
            'costs',
            'totalCostsValue',
            'installments',
            'totalAmountInstallmentsPaidToday',
            'productTotalPaidExcludingToday',
            'productTotalPaidToday',
            // 'orders',
            // 'orderes_total_amount_paid_today',
            'totalMaintenacesValue',
            'allMaintenances',
            'totalAmountInstallmentsPaidToday',
            'lastPayments'
        ));
    }

    public function getProducts()
    {
        $lastProductIdProducts = Product::max('short_ident');
        $lastProductIdQuantity = Quantity::max('short_ident');
        $short_ident = max($lastProductIdProducts, $lastProductIdQuantity) + 1;

        $header = 'إدارة المنتجات';
        $products = Product::with('type')->with('caliber')->latest()->paginate(15);

        return view('dataentry.products', compact('products', 'header'));
    }

    public function show(Request $request)
    {
        $header = 'تفاصيل منتج';
        $product = Product::with('type')->with('caliber')->where('id', $request->product)->first();
        if ($product) {
            return view('dataentry.show', compact('product', 'header'));
        } else abort(404);
    }

    public function create()
    {
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
        return view('dataentry.create', compact('header', 'types', 'calibers', 'short_ident', 'totalWeightProducts', 'lastEnteredWeight', 'quantites'));
    }

    public function createProductFromQuantity()
    {
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
        return view('dataentry.create_product_from_quantity', compact('header', 'types', 'calibers', 'short_ident', 'totalWeightProducts', 'lastEnteredWeight', 'quantites'));
    }

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
                    'price' => $request->price,
                    'ounce_price' => $request->ounce_price,
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
        if ($request->quantity_check_box) {
            return redirect()->route('dataentry.create_from_quantity')->with('message', Lang::get('alert.success_insert'));
        }
        return redirect()->route('dataentry.create')->with('message', Lang::get('alert.success_insert'));
    }

    private function calculateWeights($types, $calibers)
    {
        // Calculate weights for each type and caliber
        $weights = Product::whereIn('type_id', $types->pluck('id'))
            ->whereIn('caliber_id', $calibers->pluck('id'))
            ->where('status', '!=', 1) // Exclude sold products
            ->groupBy('type_id', 'caliber_id', 'types.name', 'calibers.full_name') // Adjust groupBy clause
            ->selectRaw('types.name as type_name, calibers.full_name as caliber_name, sum(weight) as total_weight') // Adjust selectRaw clause
            ->join('types', 'products.type_id', '=', 'types.id')
            ->join('calibers', 'products.caliber_id', '=', 'calibers.id')
            ->get();

        // Calculate total count for each type
        $totalCountByType = Product::whereIn('type_id', $types->pluck('id'))
            ->where('status', '!=', 1) // Exclude sold products
            ->groupBy('type_id')
            ->selectRaw('type_id, count(*) as total_count')
            ->get()
            ->pluck('total_count', 'type_id');

        // Format the results into the desired array structure
        $formattedWeights = [];

        foreach ($weights as $weight) {
            $formattedWeights[$weight->type_name][$weight->caliber_name] = $weight->total_weight;
        }

        // Add total count for each type to the result
        foreach ($formattedWeights as $typeName => $caliberWeights) {
            $formattedWeights[$typeName]['total'] = $totalCountByType[$types->where('name', $typeName)->first()->id] ?? 0;
        }

        return $formattedWeights;
    }

    //public statistics
    private function calculateSumWeights($types, $calibers)
    {
        $weights = Product::whereIn('type_id', $types->pluck('id'))
            ->whereIn('caliber_id', $calibers->pluck('id'))
            ->where('status', '=', 2) // Exclude sold products
            ->groupBy('caliber_id', 'calibers.full_name', 'calibers.transfarmed')
            ->selectRaw('calibers.full_name as caliber_name, sum(weight) as total_weight, count(*) as total_products, calibers.transfarmed as transfarmed')
            ->join('calibers', 'products.caliber_id', '=', 'calibers.id')
            ->get();
        // dd($weights);

        // Format the results into the desired array structure
        $formattedWeights = [];

        foreach ($weights as $weight) {
            $caliberName = $weight->caliber_name;
            $totalWeight = $weight->total_weight;
            $totalProducts = $weight->total_products;
            $transfarmedValue = $weight->transfarmed; // Get the transformed value directly from the query result

            // Calculate the multiplied value
            $multipliedValue = $totalWeight * $transfarmedValue;
            $this->totalSumAfterTransforming += $multipliedValue;

            // Store the results in the formatted array
            $formattedWeights[$caliberName] = [
                'caliber' => $caliberName,
                'total_weight' => $totalWeight,
                'total_products' => $totalProducts,
                'transfarmed_value' => $transfarmedValue,
                'multiplied_value' => $multipliedValue,
            ];
        }
        return $formattedWeights;
    }

    private function CalculateQuantitiesWeights()
    {
        $quantitiesWeights = Quantity::with('type', 'caliber')
            ->withSum('weightQuantities as total_weight', 'weight')
            ->get();
        // Iterate through each quantity and calculate the total weight before and after transformation
        $transformedWeights = $quantitiesWeights->map(function ($quantityWeight) {
            $transformedValue = $quantityWeight->caliber->transfarmed;
            $totalWeight = $quantityWeight->total_weight;
            $transfarmedValue = $quantityWeight->caliber->transfarmed * $totalWeight;
            // Calculate the total weight after transformation
            $totalWeightAfterTransformation = $totalWeight * $transformedValue;

            // Calculate the multiplied value
            // $multipliedValue = $totalWeight * $transfarmedValue;
            $this->totalSumAfterTransforming += $totalWeightAfterTransformation;
            return [
                'caliber' => $quantityWeight->caliber->full_name,
                'type' => $quantityWeight->type->name,
                'total_weight' => $totalWeight,
                'multiplied_value' =>  $transfarmedValue,
                'transfarmed_value' => $transformedValue,
            ];
        });
        return $transformedWeights;

        // Now $transformedWeights contains the total weight before and after transformation for each quantity

    }
    private function getNewProducts()
    {
        $products = Product::with('caliber', 'type', 'weightQuantity')
            ->where('ident', '!=', null)
            ->whereDate('created_at', Carbon::today())
            ->get();
            
        $totalTransfarmedWeight = 0;
    
        // Iterate through each product and filter out those with the specified status in the WeightQuantity model
        $filteredProducts = $products->filter(function ($product) {
            // Check if the product has a row in the WeightQuantity model with the specified status
            if ($product->weightQuantity && $product->weightQuantity->status == QuantitySelledTypes::FROMOLDTONEW) {
                return false; // Exclude this product
            }
            return true; // Include the product
        });
    
        // Iterate through each filtered product to calculate the total transfarmed weight
        foreach ($filteredProducts as $product) {
            $totalTransfarmedWeight += $product->weight * $product->caliber->transfarmed;
        }
    
        return [$filteredProducts, $totalTransfarmedWeight];
    }
    private function getNewQuantities()
    {
        $quantities = Quantity::with(['weightQuantities' => function ($query) {
            $query->where('weight', '>', 0)
                ->where(function ($query) {
                    $query->where('status', QuantitySelledTypes::BUYQUANTITY)
                        ->orWhere('status', QuantitySelledTypes::NEWQUANTITY);
                })
                ->whereDate('created_at', Carbon::today());
        }])->with('caliber')->with('type')->with('user')->get();
        $totalTransfarmedWeight = 0;

        // Iterate through each quantity
        foreach ($quantities as $quantity) {
            // Iterate through each weight quantity of the quantity
            foreach ($quantity->weightQuantities as $weightQuantity) {
                // Add the transfarmed weight to the total sum
                $totalTransfarmedWeight += $weightQuantity->weight * $quantity->caliber->transfarmed;
            }
        }
        return [$quantities, $totalTransfarmedWeight];
    }
    private function boughtQuantity()
    {
        $newboughtQuantites = WeightQuantity::with('quantity.caliber')->with('quantity.type')->with('user')
            ->where('status', QuantitySelledTypes::BUYQUANTITY)
            ->whereDate('created_at', Carbon::today())->get();
        return $newboughtQuantites;
    }
    private function getCosts()
    {
        $costs = Cost::with('costType')->with('user')->whereDate('created_at', Carbon::today())
            ->selectRaw('*, SUM(cost_value) OVER() as total_cost_value')
            ->get();
        return $costs;
    }
    private function getTodayMaintenanceOrders()
    {
        $totalMaintenanceValue = 0;
        // today orderes
        $newMaintenances = Maintenance::with(['customer', 'user'])->whereDate('created_at', Carbon::today())->get();
        foreach ($newMaintenances as $maintenance) {
            $maintenance->status_name = MaintenanceStatus::getStatus($maintenance->status);

            switch ($maintenance->status) {
                case MaintenanceStatus::PENDING:
                    $totalMaintenanceValue += $maintenance->cost;
                    break;
                case MaintenanceStatus::PENDINGNOTPAID:
                    $totalMaintenanceValue += 0;
                    break;
            }
        }

        $preMaintenances = Maintenance::with(['customer', 'user'])->whereDate('updated_at', Carbon::today())
            ->whereDate('created_at', '!=', Carbon::today())->get();
        $filteredPreMaintenances = $preMaintenances->filter(function ($maintenance) {
            return in_array($maintenance->status, [
                MaintenanceStatus::CANCELED,
                MaintenanceStatus::CANCELEDPAID,
                MaintenanceStatus::RECIVED,
                MaintenanceStatus::RECIVEDPREPAID
            ]);
        });

        foreach ($filteredPreMaintenances as $maintenance) {
            $maintenance->status_name = MaintenanceStatus::getStatus($maintenance->status);
            switch ($maintenance->status) {
                case MaintenanceStatus::CANCELED:
                    $totalMaintenanceValue = 0;
                    break;
                case MaintenanceStatus::CANCELEDPAID:
                    $totalMaintenanceValue -= $maintenance->cost;
                    break;
                case MaintenanceStatus::RECIVED:
                    $totalMaintenanceValue += $maintenance->last_cost;
                    break;
                case MaintenanceStatus::RECIVEDPREPAID:
                    $totalMaintenanceValue += 0;
                    break;
            }
        }

        $allMaintenances = $newMaintenances->concat($filteredPreMaintenances);
        $allMaintenances = $allMaintenances->values();
        return [
            'totalMaintenanceValue' => $totalMaintenanceValue,
            'allMaintenances' => $allMaintenances,
        ];
    }
    private function getPaymentsForToday()
    {
        // Get the start of today
        $startOfDay = Carbon::today();

        // Get all payments created today
        $payments = Payment::whereDate('created_at', $startOfDay)
            ->with(['order.product', 'order.customer'])
            ->get();

        $results = [];
        $totalAmount = 0;

        foreach ($payments as $payment) {
            // Get the associated order
            $order = $payment->order;

            // Determine if the payment was made while the order was in pending status
            $isPending = $order->status == OrderStatus::PENDING;

            // Calculate the amount to add or subtract based on the payment status
            $amount = ($payment->status == OrderStatus::CANCELED) ? -1 * $payment->amount : $payment->amount;
            $totalAmount += $amount;

            // Set adjusted_amount to 0 if the payment was made while the order was in pending status
            $adjustedAmount = $isPending ? 0 : $amount;

            // Store the result
            $results[] = [
                'order_id' => $payment->order_id,
                'customer_name' => $payment->order->customer->name ?? 'N/A',
                'product_name' => $payment->order->product->name ?? 'N/A',
                'user' => $payment->order->user->name ?? 'N/A',
                'amount_paid' => $payment->amount,
                'amount_paid_in_pending' => $order->payments->filter(function ($p) use ($order) {
                    return $p->status == OrderStatus::PENDING && $p->created_at <= $order->created_at;
                })->sum('amount'),
                'status' => OrderStatus::getStatus($payment->status),
                'status_timestamp' => $payment->created_at,
                'adjusted_amount' => $adjustedAmount,
            ];
        }
        return [
            'results' => $results,
            'total_amount' => (float)$totalAmount,  // Formatting total amount with 2 decimal places
        ];
    }
}
