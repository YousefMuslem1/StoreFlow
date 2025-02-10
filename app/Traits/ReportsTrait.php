<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quantity;
use App\Models\WeightQuantity;
use App\Enums\QuantitySelledTypes;
use Illuminate\Support\Facades\DB;

trait ReportsTrait
{


    //حساب وزن المنتجات الى 24 المتوفر او المباع عدا الخشر ك 24
    private function productsFineCalc($startDate = null, $endDate = null, $status)
    {
        $query = Product::where('status', '=', $status)
            ->where('short_ident', '!=', null) // Exclude sold products
            ->join('calibers', 'products.caliber_id', '=', 'calibers.id')
            ->select('calibers.full_name as caliber_name', 'products.weight', 'calibers.transfarmed', 'products.selled_price', 'products.id');

        if ($startDate) {
            $query->whereDate('products.selled_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('products.selled_date', '<=', $endDate);
        }

        $weights = $query->get();

        $totalWeightAfterTransforming = 0;
        $totalWeightBeforeTransforming = 0;
        $totalValueForSelledProducts = 0;
        $productsInstallmentRemaining = 0;
        foreach ($weights as $weight) {
            $totalWeight = $weight->weight;
            $transfarmedValue = $weight->transfarmed; // Get the transformed value directly from the query result

            // Calculate the multiplied value
            $multipliedValue = $totalWeight * $transfarmedValue;
            $totalWeightAfterTransforming += $multipliedValue;
            $totalWeightBeforeTransforming += $totalWeight;

            // Check if the product has installments
            $product = Product::with('installments')->find($weight->id);
            if ($product->installments->isNotEmpty()) {
                $amountPaid = $product->installments->sum('amount_paid');
                $totalValueForSelledProducts += $amountPaid;
                $productsInstallmentRemaining +=  $weight->selled_price - $amountPaid;
            } else {
                $totalValueForSelledProducts += $weight->selled_price;
            }
        }

        return [$totalWeightAfterTransforming, $totalWeightBeforeTransforming, $totalValueForSelledProducts, $productsInstallmentRemaining];
    }


    // جلب الخشر المباع او المتوفر ك 24
    public function quantitiesFineCalc($startDate = null, $endDate = null, $status = null)
    {
        $query = Quantity::with(['caliber', 'weightQuantities' => function ($query) use ($startDate, $endDate, $status) {
            if ($status !== null) {
                $query->where('status', $status);
            }
            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }
        }])
            ->withSum(['weightQuantities as total_weight' => function ($query) use ($startDate, $endDate, $status) {
                if ($status !== null) {
                    $query->where('status', $status);
                }
                if ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                }
            }], 'weight');

        $quantitiesWeights = $query->get();

        $totalWeightAfterTransformation = 0;
        $totalWeightbeforeTransforming = 0;
        $totalBoughtPrice = 0;

        foreach ($quantitiesWeights as $quantityWeight) {
            $transformedValue = $quantityWeight->caliber->transfarmed;
            $totalWeight = $quantityWeight->total_weight;
            $totalWeightAfterTransformation += $totalWeight * $transformedValue;
            $totalWeightbeforeTransforming += $totalWeight;

            // If status matches, add to totalBoughtPrice
            if ($status == QuantitySelledTypes::BUYQUANTITY) {
                // Ensure price is loaded
                if (isset($quantityWeight->price)) {
                    $totalBoughtPrice += $quantityWeight->price;
                } else {
                    // If price is part of weightQuantities, sum it up
                    foreach ($quantityWeight->weightQuantities as $weightQuantity) {
                        if (isset($weightQuantity->price)) {
                            $totalBoughtPrice += $weightQuantity->price;
                        }
                    }
                }
            }
        }

        return [$totalWeightAfterTransformation, $totalWeightbeforeTransforming, $totalBoughtPrice];
    }


    //جلب مبيعات الخشر

    public function quantitiesSelled($startDate = null, $endDate = null)
    {
        $query = Product::where('short_ident', '=', null)
            ->join('calibers', 'products.caliber_id', '=', 'calibers.id')
            ->select('calibers.full_name as caliber_name', 'products.weight', 'calibers.transfarmed', 'products.selled_price', 'products.id');

        if ($startDate) {
            $query->whereDate('products.selled_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('products.selled_date', '<=', $endDate);
        }

        $weights = $query->get();
        $totalValueForSelledQuantity = 0;
        $quantitityInstallmentRemaining = 0;
        foreach ($weights as $weight) {
            // Check if the product has installments
            $product = Product::with('installments')->find($weight->id);
            if ($product->installments->isNotEmpty()) {
                $amountPaid = $product->installments->sum('amount_paid');
                $totalValueForSelledQuantity += $amountPaid;
                $quantitityInstallmentRemaining +=  $weight->selled_price - $amountPaid;
            } else {
                $totalValueForSelledQuantity += $weight->selled_price;
            }
        }

        return [$totalValueForSelledQuantity, $quantitityInstallmentRemaining];
    }

    // العربون
    private function orderCalc($startDate = null, $endDate = null)
    {
        // Set endDate to today's date if it is null
        $endDate = $endDate ?? Carbon::now()->toDateString();

        // Initialize the payments query
        $paymentsQuery = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->select(DB::raw('payments.status, SUM(payments.amount) as total_amount, COUNT(*) as payment_count'))
            ->groupBy('payments.status')
            ->orderBy('payments.status');

        // Apply startDate filter if provided
        if ($startDate) {
            $paymentsQuery->whereDate('payments.created_at', '>=', $startDate);
        }

        // Apply endDate filter
        $paymentsQuery->whereDate('payments.created_at', '<=', $endDate);

        // Execute the payments query
        $paymentsGroupedByStatus = $paymentsQuery->get();

        // Initialize the orders query
        $ordersQuery = Order::with(['payments', 'customer']);

        // Apply startDate filter if provided
        if ($startDate) {
            $ordersQuery->whereDate('created_at', '>=', $startDate);
        }

        // Apply endDate filter
        $ordersQuery->whereDate('created_at', '<=', $endDate);

        // Execute the orders query
        $orders = $ordersQuery->get();

        return response()->json(['total' => $paymentsGroupedByStatus, 'orders' => $orders]);
    }
}
