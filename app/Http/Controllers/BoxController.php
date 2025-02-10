<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Box;
use Illuminate\Http\Request;
use App\Enums\UserRoleStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BoxController extends Controller
{

    public function index()
    {
        
        $header = 'إدارة الصندوق';
        $box = Box::with('user')->latest()->paginate(10);
        // dd($box);
        return view('box.index', compact('header', 'box'));
    }

    public function store(Request $request)
    {
        // Check if a record already exists for today
        $existingRecord = Box::whereDate('created_at', Carbon::today())->first();

        if ($existingRecord) {
            // Update the existing record
            //    $existingRecord->update([
            //        'opened_box' => $request->input('opened_box'),
            //    ]);

            return response()->json(['message' => 'صندوق اليوم تم إنشاءه مسبقاً!'], 403);
        } else {
            // Create a new record
            $newRecord = Box::create([
                'opened_box' => $request->input('opened_box'),
                'user_id' => Auth::user()->id
            ]);

            return response()->json(['message' => 'تم إضافة صندوق اليوم بقيمة ' .  $request->input('opened_box') . ' euro']);
        }
    }
}
