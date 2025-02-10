<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\Caliber;
use App\Models\Product;
use App\Models\Quantity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class CaliberTransormation extends Controller
{
    public function index()
    {
        $header = 'التحويل ل عيار 24';
        $calibers = Caliber::all();
        $types = Type::all();
        return view('caliber_transformation.index', compact('header', 'types', 'calibers'));
    }

    //get transformation result
    public function transformatinResult(Request $request)
    {
        // Retrieve the transformed field from the caliber table
        $selectedType = $request->input('type');
        $selectedCaliber = $request->input('caliber');

        if ($request->input('filterType') == 'allProducts') {
            // Retrieve the transformed field from the caliber table
            $calibers = Caliber::when($selectedCaliber, function ($query) use ($selectedCaliber) {
                return $query->where('id', $selectedCaliber);
            })->get();

            $types = Type::when($selectedType, function ($query) use ($selectedType) {
                return $query->where('id', $selectedType);
            })->get();

            // Initialize an associative array to store the sums for each caliber and type combination
            $sums = [];

            foreach ($calibers as $caliber) {
                foreach ($types as $type) {
                    if ($type->is_quantity) {
                        // Retrieve all quantities for the current caliber and type combination
                        $quantities = Quantity::with('weightQuantities')->where('caliber_id', $caliber->id)
                            ->where('type_id', $type->id)
                            ->get();
                        // Initialize the sum of weights for quantities
                        $sumOfWeights = 0;

                        if($quantities){
                            
                        }
                        // Calculate the sum of weights for quantities
                        foreach ($quantities as $quantity) {
                            $sumOfWeights += $quantity->weightQuantities->sum('weight');
                        }
                        // Perform the multiplication and calculate the sum for each product
                        $sumForCaliberAndType = $quantities->sum(function ($quantity) use ($caliber) {
                            return $caliber->transfarmed * $quantity->weightQuantities->sum('weight');
                        });
                        // Store the sum for the current caliber and type combination in the array
                        $sums[$caliber->full_name][$type->name] = ['sum' => $sumForCaliberAndType, 'original' => $quantity->weightQuantities->sum('weight')];
                    } else {
                        // Retrieve all products for the current caliber and type combination
                        $products = Product::where('status', 2)
                            ->where('caliber_id', $caliber->id)
                            ->where('type_id', $type->id)
                            ->get();

                        // Get the sum of all weights for the current caliber
                        $sumOfWeights = $products->sum('weight');

                        // Perform the multiplication and calculate the sum for each product
                        $sumForCaliberAndType = $products->sum(function ($product) use ($caliber) {
                            return $caliber->transfarmed * $product->weight;
                        });

                        // Store the sum for the current caliber and type combination in the array
                        $sums[$caliber->full_name][$type->name] = ['sum' => $sumForCaliberAndType, 'original' => $sumOfWeights];
                    }
                }
            }
            return response()->json($sums);
            // Now $sums contains the sums of transformed weights for each caliber and type combination
            // You can return or further process the array of sums here
            $html = View::make('caliber_transformation.result', compact('sums', 'calibers'))->render();
            return response($html);
        }
    }


    // public function transformatinResult(Request $request)
    // {
    //     // Retrieve the transformed field from the caliber table
    //     $selectedType = request('type');
    //     $selectedCaliber = request('caliber');
    //     if ($request->filterType == 'allProducts') {
    //         // Retrieve the transformed field from the caliber table
    //         if ($selectedCaliber) {
    //             $calibers = Caliber::where('id', $selectedCaliber)->get();
    //         } else {
    //             $calibers = Caliber::all();
    //         }

    //         if ($selectedType) {
    //             $types = Type::where('id', $selectedType)->get();
    //         } else {
    //             $types = Type::all();
    //         }

    //         // Initialize an associative array to store the sums for each caliber and type combination
    //         $sums = [];

    //         foreach ($calibers as $caliber) {
    //             foreach ($types as $type) {
    //                 // Retrieve all products for the current caliber and type combination

    //                 $products = Product::where('status', 2)
    //                     ->where('caliber_id', $caliber->id)
    //                     ->where('type_id', $type->id)
    //                     ->get();

    //                 // Get the sum of all weights for the current caliber
    //                 $sumOfWeights = $products->sum('weight');

    //                 // Perform the multiplication and calculate the sum for each product
    //                 $sumForCaliberAndType = $products->sum(function ($product) use ($caliber) {
    //                     return $caliber->transfarmed * $product->weight;
    //                 });
    //                 // Store the sum for the current caliber and type combination in the array
    //                 $sums[$caliber->name][$type->name] = ['sum' => $sumForCaliberAndType, 'original' => $sumOfWeights];
    //             }
    //         }

    //         // return $sums;
    //         // Now $sums contains the sums of transformed weights for each caliber and type combination
    //         // You can return or further process the array of sums here
    //         $html = View::make('caliber_transformation.result', compact('sums', 'calibers'))->render();
    //         return response($html);
    //     }
    // }
}
