<?php

namespace App\Http\Controllers;

use App\Models\Caliber;
use Illuminate\Http\Request;
use App\Enums\UserRoleStatus;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\TransformationValue;
use Illuminate\Support\Facades\Lang;

class CaliberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $calibers = Caliber::latest()->paginate(5)->withQueryString();
        $header = Lang::get('caliber.header');
        // dd($calibers);
        // dd($calibers);
        return view('calibers.index', compact('calibers', 'header'));
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
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {

            $request->validate([
                'caliber' => 'required|integer', //|unique:calibers,name
                'price' => 'required|numeric',
                'transfarmed' => 'required|numeric',
                'full_name' => ['required', Rule::unique('calibers', 'full_name')->ignore($request->id)],
            ]);
            $caliber = Caliber::create([
                'name' => $request->caliber,
                'caliber_price' => $request->price,
                'transfarmed' => $request->transfarmed,
                'full_name' => $request->full_name,
            ]);

            // TransformationValue::create([
            //     'caliber_id' => $caliber->id,
            //     'value' => $request->transfarmed,
            // ]);
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
            return response()->json(['success' => true, 'data' => $request->name], 200);
        } else return abort(403);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request)
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {
            $request->validate([
                'value' => 'required', 'integer',
                'updatedPrice' => 'required|numeric',
                'updatedTransfarmed' => 'required|numeric',
                'fullname' => ['required', Rule::unique('calibers', 'full_name')->ignore($request->id)],
            ]);
            $data = Caliber::where('id', $request->id)->first()?->update([
                'name' => $request->value,
                'caliber_price' => $request->updatedPrice,
                'transfarmed' => $request->updatedTransfarmed,
                'full_name' => $request->fullname,

            ]);
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
            return response()->json(['data' => $data, 'status' => 'success'], 201);
        } else return abort(403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $data = Caliber::destroy($id);
        // flash()
        //     ->translate(session()->get('locale'))
        //     ->addSuccess(Lang::get('alert.success_deleted'), Lang::get('alert.successfully'));
        // return response()->json(['data' => $data, 'status' => 'success'], 200);
    }
}


