<?php

namespace App\Http\Controllers;

use App\Models\CostType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class CostsTypesController extends Controller
{
    public function index()
    {
        $header = 'أنواع المصاريف';
        $types = CostType::latest()->paginate(10);
        return view('costs.types.index', compact('header', 'types'));
    }

    public function create()
    {
        $header = 'إنشاء نوع مصروف جديد';
        return view('costs.types.create', compact('header'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|unique:cost_types,type'
        ]);

        CostType::create([
            'type' => $request->type
        ]);

        flash()
            ->translate(session()->get('locale'))
            ->addSuccess(Lang::get('alert.success_insert'), Lang::get('alert.successfully'));
        return redirect()->back();
    }
}
