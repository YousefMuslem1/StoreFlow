<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\Caliber;
use App\Models\Product;
use App\Models\Quantity;
use App\Enums\ProductStatus;
use Illuminate\Http\Request;
use App\Enums\UserRoleStatus;
use App\Models\WeightQuantity;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class InvantoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {

            $header = 'الجرد';
            $types = Type::where('is_quantity', false)->get();
            $calibers = Caliber::all();
            return view('invantory.index', compact('header', 'types', 'calibers'));
        }
        return abort(403);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        // dd($request->toArray());

        return view('invantory.results', ['results' => $request]);
    }


    public function resultCalculator(Request $request)
    {
        // return $request;
        $isChecked = filter_var($request->input('checkAllProducts'), FILTER_VALIDATE_BOOLEAN);

        // $user_data = $request->itemsArray;
        $typeFilter = $request->input('type', 0); // Default to 0 if not provided
        $caliberFilter = $request->input('caliber', 0); // Default to 0 if not provided
        $notAvSum = 0; // الوزن الناقص
        $countOfStolenPieces = 0;
        $user_data = [];
        $notfoundIdent = [];
        // if ($isChecked) {
        // }
        //  read file excel 
        if ($request->hasFile('excelFile') && $request->file('excelFile')->isValid()) {
            $file = $request->file('excelFile');

            // Validate the file if needed

            // Read the Excel file
            $data = Excel::toArray([], $file);
            // Loop through the rows of data, starting from the second row (index 1)
            for ($i = 1; $i < count($data[0]); $i++) {
                // Get the value of the EPC column from the current row
                $epc = $data[0][$i][2] ?? null; // Assuming "EPC" column is at index 2

                // Add the EPC value to the array if it's not empty
                if (!empty($epc)) {
                    $user_data[] = $epc;
                }
            }

            for ($i = 1; $i < count($data[0]); $i++) {
                // Get the value of the EPC column from the current row
                $epc = $data[0][$i][3] ?? null; // Assuming "EPC" column is at index 2

                // Add the EPC value to the array if it's not empty
                if (!empty($epc)) {
                    $user_data[] = $epc;
                }
            }
            // textarea proccess 
            if($request->input('addtionItems')){
                $additionItems =$request->input('addtionItems');
                
                $additionItemsArray = explode(",", $request->input('addtionItems')[0]);
                $user_data = array_merge($user_data, $additionItemsArray);
            }

            // return $user_data;
            
            // Extract the column named "EPC"   

        } elseif(!$request->hasFile('excelFile')){
            
            if($request->input('addtionItems')){
                $additionItems =$request->input('addtionItems');
                $user_data = explode(",", $request->input('addtionItems')[0]);

            }
        }

        // Get count of available products based on both type and caliber filters using AND condition
        $countWithFilters = Product::where('status', ProductStatus::AVAILABLE)
            ->when($typeFilter > 0, function ($query) use ($typeFilter) {
                return $query->where('type_id', $typeFilter);
            })
            ->when($caliberFilter > 0, function ($query) use ($caliberFilter) {
                return $query->where('caliber_id', $caliberFilter);
            })
            ->count();

        // Count the number of quantities based on the filters
        $quantitiesCount = Quantity::when($typeFilter > 0, function ($query) use ($typeFilter) {
            return $query->where('type_id', $typeFilter);
        })
            ->when($caliberFilter > 0, function ($query) use ($caliberFilter) {
                return $query->where('caliber_id', $caliberFilter);
            })->count();
        $countWithFilters += $quantitiesCount;

        // weight sum for all filtered [products]
        $weightSum = Product::where('status', ProductStatus::AVAILABLE)
            ->when($typeFilter > 0, function ($query) use ($typeFilter) {
                return $query->where('type_id', $typeFilter);
            })
            ->when($caliberFilter > 0, function ($query) use ($caliberFilter) {
                return $query->where('caliber_id', $caliberFilter);
            })
            ->sum('weight');

        $weightSumQuantity = Quantity::when($typeFilter > 0, function ($query) use ($typeFilter) {
            return $query->where('type_id', $typeFilter);
        })
            ->when($caliberFilter > 0, function ($query) use ($caliberFilter) {
                return $query->where('caliber_id', $caliberFilter);
            })->withSum('weightQuantities', 'weight')
            ->get()
            ->sum('weight_quantities_sum_weight');
        $weightSum += $weightSumQuantity;


        // Retrieve all items from the database
        $db_data = Product::with('type')->with('caliber')->with('user')
            ->when($typeFilter > 0, function ($query) use ($typeFilter) {
                return $query->where('type_id', $typeFilter);
            })
            ->when($caliberFilter > 0, function ($query) use ($caliberFilter) {
                return $query->where('caliber_id', $caliberFilter);
            })
            ->get();
            
        $quantities = Quantity::when($typeFilter > 0, function ($query) use ($typeFilter) {
            return $query->where('type_id', $typeFilter);
        })
            ->when($caliberFilter > 0, function ($query) use ($caliberFilter) {
                return $query->where('caliber_id', $caliberFilter);
            })->withSum('weightQuantities', 'weight')->get();


        // Convert the collection to an array for easier comparison
        $filtered_data = [];
        foreach ($db_data as $product) {
            $formattedSelledDate = Carbon::parse($product->selled_date)->format('Y-m-d');

            // Check if the product sold today
            if ($product->status == ProductStatus::SOLD && $formattedSelledDate == Carbon::today()->format('Y-m-d')) {
                $filtered_data[$product->id] = $product;
                $filtered_data[$product->id]['condition'] = 'sold';
            }
            $arrayItems = []; 
            // Check if the product is stolen
            // If the product is in the database, is not in user_data array, and status is 2 (Available)
            if ($product->status == ProductStatus::AVAILABLE && (!in_array($product->short_ident, $user_data) && !in_array($product->ident, $user_data))) {
                $filtered_data[$product->id] = $product;
                $filtered_data[$product->id]['condition'] = 'notavailable';
                $notAvSum += $product->weight; // الوزن الناقص
                $countOfStolenPieces  +=   1;
            }
            // Check if the product is available in the quantity table
            // Check if all products are selected, regardless of status
            if ($isChecked && $product->status == ProductStatus::AVAILABLE) {
                // Check if the product with the same ID already exists in $filtered_data
                $existingProduct = collect($filtered_data)->where('id', $product->id)->first();

                if (!$existingProduct) {
                    // The product doesn't exist in $filtered_data, so add it
                    $filtered_data[$product->id] = $product;
                    $filtered_data[$product->id]['condition'] = 'available';
                }
            }
        }
        foreach ($quantities as $quantity) {
            if (!in_array($quantity->ident, $user_data) && !in_array($quantity->short_ident, $user_data)) {
                $filtered_data[$quantity->id] = $quantity;
                $filtered_data[$quantity->id]['condition'] = 'notavailable';
                $notAvSum += WeightQuantity::where('quantity_id', $quantity->id)->sum('weight'); // الوزن الناقص
                $countOfStolenPieces  +=   1;
            }
        }
        // Order the $filtered_data array based on the "condition" key
        usort($filtered_data, function ($a, $b) {
            $conditionOrder = [
                'notavailable' => 1,
                'sold' => 2,
                'available' => 3,
            ];

            return $conditionOrder[$a['condition']] - $conditionOrder[$b['condition']];
        });

        $filtered_data = array_values($filtered_data); // Reset array keys to avoid gaps


        $html = View::make('invantory.results', compact('filtered_data'))->render();
        return response()->json([
            'html' => $html,
            'count' => $countWithFilters ,
            'sum' => number_format($weightSum, 2, '.', '') . 'غ',
            'notAvSum' => number_format($notAvSum, 2, '.', '') . 'غ', // الوزن الناقص في الجرد
            'countAvSum' => $countOfStolenPieces,
            'readItems' => count($user_data) / 2,
            'filtered_data' => $filtered_data,

        ]);
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
}
