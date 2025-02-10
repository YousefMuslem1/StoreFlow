<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\SavedInvantory;
use App\Models\SavedInvantoryDate;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SavedInvantoryController extends Controller
{
    // display invantory information
    public function index(Request $request)
    {
        $header = 'الجرد - المحفوظات';
        $invantories = SavedInvantoryDate::with('type')->with('caliber')->paginate(10);
        return view('saved_invantory.index', compact('header', 'invantories'));
    }

    public function getInvantoryDetails(SavedInvantoryDate $invantory)
    {
        $header =  'الجرد - تفاصيل الجرد ' . Carbon::parse($invantory->created_at)->format('d.m.Y - H:i');;
        $invantoryDetialsItems = SavedInvantory::where('saved_invantory_date_id', $invantory->id)->with('caliber')->with('type')->paginate(5);
        // $invantory->created_at  = Carbon::parse( $invantory->created_at)->format('d.m.Y الساعة H:i');
        return view('saved_invantory.invantory_details', compact('header', 'invantory', 'invantoryDetialsItems'));

    }


    // Save invantory information
    public function saveInvantory(Request $request)
    {
        $statusMapping = [
            'notavailable' => 3,
            'available' => 2,
            'sold' => 1,
        ];

        try {
            DB::transaction(function () use ($request, $statusMapping) {
                // Operation 1: Insert data into the SavedInvantoryDate
                $invantoryCategory = SavedInvantoryDate::create([
                    'type_id' => $request->typeFilter,
                    'caliber_id' => $request->caliberFilter,
                ]);

                // Operation 2: Insert data into the SavedInvantory
                foreach ($request->allResponseItems as $product) {
                    // Create a new inventory record for each product
                    SavedInvantory::create([
                        'saved_invantory_date_id' => $invantoryCategory->id,
                        'product_id' => $product['id'],
                        'type_id' => $product['type_id'],
                        'caliber_id' => $product['caliber_id'],
                        'type_filter' => $request->typeFilter,
                        'caliber_filter' => $request->caliberFilter,
                        'status' => $statusMapping[$product['condition']],
                    ]);
                }
            });
            // Both operations were successful, the transaction is committed.
            return response()->json('success', 200);
        } catch (\Exception $e) {
            // An error occurred, the transaction is rolled back.
            // Handle the exception or log the error.
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
