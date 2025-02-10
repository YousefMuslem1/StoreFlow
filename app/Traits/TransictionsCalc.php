<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Box;
use App\Models\Cost;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Models\Installment;
use App\Models\Maintenance;
use App\Models\WeightQuantity;
use App\Enums\MaintenanceStatus;
use App\Enums\QuantitySelledTypes;

trait TransictionsCalc
{
    // حساب المنتجات المباعة ف تاريخ محدد
    public function totalSelledProductsValue($startDate, $enddate = null)
    {
        $selled_products = Product::with(['caliber', 'type', 'user', 'installments', 'order'])
            ->where('selled_date', '>=', $startDate)
            ->where(function ($query) {
                $query->whereDoesntHave('order')
                    ->orWhereHas('order', function ($query) {
                        $query->where('status', OrderStatus::CANCELED); 
                    });
            })
            ->orderBy('updated_at', 'asc')
            ->get();
        $installments = Installment::with(['product.type', 'customer'])
            ->whereDate('created_at', $startDate)
            ->get();

        $totalAmountInstallmentsPaidToday = $installments->sum('amount_paid');

        // Fetch all installments excluding today
        $previousInstallments = Installment::with(['product', 'customer'])
            ->whereDate('created_at', '<', $startDate)
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

        $selledTodayPriceTotal = $selled_products->reduce(function ($carry, $product) {
            if ($product->installments->isNotEmpty()) {
                // Sum of installments' amount paid
                return $carry ; 
            } else {
                // Directly using selled price
                return $carry + $product->selled_price;
            }
        }, 0);
        return $selledTodayPriceTotal;
    }

    //حساب قيمة عربون
    public function totalOrdersValue($startDate, $endDate = null)
    {
        // Get all payments created today
        $payments = Payment::whereDate('created_at', $startDate)
            ->with(['order.product', 'order.customer'])
            ->get();

            $totalAmount = 0;
    
            foreach ($payments as $payment) {
                // Get the associated order
                $order = $payment->order;
        
                // Determine if the payment was made while the order was in pending status
                $isPending = $order->status == OrderStatus::PENDING;
        
                // Calculate the amount to add or subtract based on the payment status
                $amount = ($payment->status == OrderStatus::CANCELED) ? -1 * $payment->amount : $payment->amount;
                $totalAmount += $amount;
        
        
                // Store the result
            }
               return (float) $totalAmount;  // Formatting total amount with 2 decimal places
    }

    //جلب قيمة صنجدوق
    public function boxOpenedValue($startDate)
    {
        $boxValue = 0;
        $yesterdayBoxOpenedValue = Box::whereDate('created_at', $startDate)->first();
        if ($yesterdayBoxOpenedValue) {
            $boxValue = $yesterdayBoxOpenedValue->opened_box;
        }

        return $boxValue;
    }

    //قيمة المشتريات
    public function boughtQuantityValue($startDate, $enddate = null)
    {
        $newboughtQuantites = WeightQuantity::where('status', QuantitySelledTypes::BUYQUANTITY)
            ->whereDate('created_at', $startDate)->get();

        $newBoughtPrice = $newboughtQuantites->sum('price');
        return $newBoughtPrice;
    }

    //مصاريف
    public function costsValue($startDate, $endDate = null)
    {
        $costs = Cost::whereDate('created_at', $startDate)
            ->selectRaw('*, SUM(cost_value) OVER() as total_cost_value')
            ->get();
        $totalCostsValue = $costs->isNotEmpty() ? $costs->first()->total_cost_value : 0;

        // dd($totalCostsValue);
        return $totalCostsValue;
    }

    //تقسيط
    public function installmentsTotalValue($startDate)
    {
        $installments = Installment::with(['product.type', 'customer'])
            ->whereDate('created_at', $startDate)
            ->get();

        $totalAmountInstallmentsPaidToday = $installments->sum('amount_paid');

        return $totalAmountInstallmentsPaidToday;
    }


    public function maintenancesValue($startDate, $endDate = null)
    {
        $totalMaintenanceValue = 0;
        // today orderes
        $newMaintenances = Maintenance::whereDate('created_at', $startDate)->get();
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

        $preMaintenances = Maintenance::whereDate('updated_at', $startDate)
            ->whereDate('created_at', '!=', $startDate)->get();
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

        return  $totalMaintenanceValue;
        // return [
        //     'totalMaintenanceValue' => $totalMaintenanceValue,
        //     'allMaintenances' => $allMaintenances,
        // ];
    }
}
