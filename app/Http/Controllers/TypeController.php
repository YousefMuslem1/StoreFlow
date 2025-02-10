<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;
use App\Enums\UserRoleStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $header = Lang::get('types.header');
        $types = Type::latest()->paginate(12);
        return view('types.index', compact('header', 'types'));
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
                'type' => 'required|string|unique:types,name',
                'is_quantity' => 'required'
            ]);
            Type::create([
                'name' => $request->type,
                'is_quantity' => $request->is_quantity
            ]);
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
            return response()->json(['success' => true, 'data' => $request->type], 200);
        } else  return abort(403);
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
                'value' => 'required|unique:types,name',
            ]);
            $data = Type::where('id', $request->id)->update([
                'name' => $request->value
            ]);
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
            return response()->json(['data' => $data, 'status' => 'success'], 201);
        } else  return abort(403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {

            $data = Type::destroy($id);
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_deleted'), Lang::get('alert.successfully'));
            return response()->json(['data' => $data, 'status' => 'success'], 200);
        } else  return abort(403);
    }
}
