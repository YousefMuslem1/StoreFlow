<?php

namespace App\Http\Controllers;

use App\Models\Cost;
use App\Models\CostType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\SendTelegramMessageJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CostsController extends Controller
{
    public function index(Request $request)
    {
        $header = 'المصاريف';
        $types = CostType::latest()->get();

        // Base query for pagination
        $costsQuery = Cost::with('costType', 'user')->latest();

        // Filter costs based on selected type if applicable
        if ($request->has('cost_type_id') && $request->cost_type_id != '') {
            $costsQuery->where('type_id', $request->cost_type_id);
        }

        // Calculate total positive and negative sums separately
        $totalPositiveSumQuery = Cost::where('cost_value', '>', 0);
        $totalNegativeSumQuery = Cost::where('cost_value', '<', 0);

        // Apply filter if applicable
        if ($request->has('cost_type_id') && $request->cost_type_id != '') {
            $totalPositiveSumQuery->where('type_id', $request->cost_type_id);
            $totalNegativeSumQuery->where('type_id', $request->cost_type_id);
        }

        // Get sums
        $totalPositiveSum = $totalPositiveSumQuery->sum('cost_value');
        $totalNegativeSum = $totalNegativeSumQuery->sum('cost_value');

        // Paginate costs
        $costs = $costsQuery->paginate(10);

        // Return view with data
        return view('costs.index', compact('header', 'costs', 'types', 'totalPositiveSum', 'totalNegativeSum'));
    }



    public function store(Request $request)
    {
        $transictionType = $request->transictionType;
        $cost_value = $request->value;
        if ($transictionType == 1) { // withdraw
            $cost_value = $cost_value * -1;
        }
        $cost = Cost::create([
            'type_id' => $request->type,
            'user_id' => Auth::user()->id,
            'cost_value' => $cost_value,
            'note' => $request->note
        ]);

        $message = "---------مصاريف----------"
            . "\nالنوع: " . $cost->costType->type
            . "\n القيمة :" . $cost->cost_value . " €"
            // ."\n العملية: " . $cost->cost_value > 0 ? 'ايداع' : 'سحب'
            . "\n المدخل :" . $cost->user->name
            . "\n ملاحظة: " . $cost->note;

        if ($cost->cost_value  > 0)
            $message .= "\n العملية: إيداع";
        else
            $message .= "\n العملية: سحب";

            SendTelegramMessageJob::dispatch($message);


        return response()->json(['message' => 'تم اضافة مصروف بقيمة' . $request->value . ' يورو' . 'الى قائمة مصاريف اليوم'], 200);
    }

    public function destroy(Request $request)
    {
        // dd($request);
        try {
            // Find the cost by ID
            $cost = Cost::findOrFail($request->cost);
            $message = "------- الغاء مصروف --------"
            . "\nالنوع: " . $cost->costType->type
            . "\n القيمة :" . $cost->cost_value . " €"
            // ."\n العملية: " . $cost->cost_value > 0 ? 'ايداع' : 'سحب'
            . "\n المدخل :" . $cost->user->name
            . "\n ملاحظة: " . $cost->note;
            // Perform the deletion
            $cost->delete();
           
            SendTelegramMessageJob::dispatch($message);

            // Redirect back with success message
            return Redirect::back()->with('success', 'تم حذف التكلفة بنجاح');
        } catch (\Exception $e) {
            // Log the error and redirect back with an error message
            Log::error('Error deleting cost: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Error deleting cost. Please try again.');
        }
    }
}
