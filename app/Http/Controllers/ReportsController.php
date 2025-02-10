<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Enums\QuantitySelledTypes;
use App\Traits\ReportsTrait;
use Illuminate\Http\Request;
use App\Traits\TransictionsCalc;
use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    use TransictionsCalc, ReportsTrait;
    public function index()
    {
           // $this->productsFineCalc(null, null, ProductStatus::SOLD);
        //    $this->quantitiesFineCalc(null, null, QuantitySelledTypes::SELLEDODD);
        return view('reports.index');
    }

    public function getResult(Request $request)
    {

        $setResult = true;
        $datefrom = $request->from;
        $dateto = $request->to;
 
        //get selled products without quantity AS 24 g and normal
        $products_weights = $this->productsFineCalc($datefrom, $dateto, ProductStatus::SOLD);

        //selled weight quantities
        $quantities = $this->quantitiesFineCalc($datefrom, $dateto, QuantitySelledTypes::SELLEDODD);
        // bought weight quantity 
        $quantities_bought = $this->quantitiesFineCalc($datefrom, $dateto, QuantitySelledTypes::BUYQUANTITY);
        // المبلغ الاجمالي المباع للكمية
        $selled_total_quantities = $this->quantitiesSelled($datefrom, $dateto);

        $orders_calc = $this->orderCalc($datefrom, $dateto);
        $ordersTotal = $orders_calc->getData()->total;
        $ordersDetails = $orders_calc->getData()->orders;

        return redirect()->back()->withInput()->with([
            'setResult' => $setResult,
            'products_weights_as_fine' => $products_weights[0],
            'totalWeightbeforeTransforming' => $products_weights[1], 
            'total_selled_product_price' => $products_weights[2],
            'productsInstallmentRemaining' => $products_weights[3],
            'quantities_as_fine' => $quantities[0] * -1,
            'quantities_as_total' => $quantities[1] * -1,
            'quantities_as_fine_bought' => $quantities_bought[0],
            'quantities_as_total_bought' => $quantities_bought[1],
            'totalBoughtPrice' => $quantities_bought[2],
            'quantities_selled_total' => $selled_total_quantities[0],
            'quantitityInstallmentRemaining' => $selled_total_quantities[1],
            'orders_total' => $ordersTotal,   // Passing the orders total
            'orders_details' => $ordersDetails  // Passing the orders details 
        ]);

    }
}
