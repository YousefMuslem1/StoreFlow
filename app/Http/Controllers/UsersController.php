<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\UserRoleStatus;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {

            $header = 'إدارة الموظّفين';
            $users = User::withTrashed()->latest()->paginate(10);
            return view('users.index', compact('header', 'users'));
        } else return abort(403);
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
                'name' => ['required', 'string', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed',  Rules\Password::defaults()],
                'status' => ['required', 'in:' . UserRoleStatus::ADMIN . ',' . UserRoleStatus::EMPLOYER . ',' . UserRoleStatus::WATCHER . ',' . UserRoleStatus::DATAENTRY],
            ]);

            User::create([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'type' => $request->status
            ]);
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
            return response()->json('success', 201);
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
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);
        // Update the user's name and password
        $user->name = $request->input('name');
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'تم تحديث البيانات بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        if (auth()->user()->type ==  UserRoleStatus::ADMIN) {

            $user = User::withTrashed()->whereId($request->user)->first();
            if($user->status == UserRoleStatus::ACTIVE){
                $user->status = UserRoleStatus::NOT_ACTIVE;
                $user->save();
            } else {
                $user->status = UserRoleStatus::ACTIVE;
                $user->save();
            }
            // if ($user->deleted_at !== null) {
            //     $user->deleted_at = null;
            //     $user->status = 1;

            //     $user->save();
            // } else {
            //     $user->status = 2;
            // } 
            flash()
                ->translate(session()->get('locale'))
                ->addSuccess(Lang::get('alert.success_deleted'), Lang::get('alert.success_updated'));
            return redirect()->back();
        } else return abort(403);
    }
}
