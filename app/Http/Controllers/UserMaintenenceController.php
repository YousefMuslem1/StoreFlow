<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use App\Enums\MaintenanceStatus;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserMaintenenceController extends Controller
{
    //
    public function index()
    {
        $orders = Maintenance::with(['user', 'customer'])->orderBy('updated_at', 'desc')->paginate(10);
        foreach ($orders as $order) {
            $order->status_name = MaintenanceStatus::getStatus($order->status);
        }
        return view('employer.maintenance.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customerName' => 'required',
            // 'customerPhone' => 'required',
            'weight' => 'required|numeric',
            'cost' => 'required|numeric',
            'notice' => 'nullable|string',
            'product_images' => 'array',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif', // Validate each image
        ]);
        // customer is already registered
        $imagePaths = [];
        if ($request->customerIsNew == 'false') {

            $customer = Customer::where('id', $request->customer_id)->first();
            try {
                DB::transaction(function () use (&$order, $customer, $request, $imagePaths) {
                    if ($request->hasFile('product_images')) {
                        foreach ($request->file('product_images') as $image) {
                            // Store the image and get its path
                            $path = $image->store('product_images', 'public');
                            $imagePaths[] = $path; // Add the path to the array
                        }
                    } 

                    $validatedData['product_images'] = json_encode($imagePaths);
                    $order = Maintenance::create([
                        'customer_id' => $customer->id,
                        'user_id' => Auth::user()->id,
                        'weight' => $request->weight,
                        'cost' => $request->cost,
                        'status' => $request->notPaid == 'true' ? MaintenanceStatus::PENDINGNOTPAID : MaintenanceStatus::PENDING,
                        'product_images' => $validatedData['product_images'],
                        'notice' => 'معلومات انشاء الطلب: ' . '<br/>' . $request->notice . '<hr/>'
                    ]);
                });


                $order = Maintenance::where('id', $order->id)->with(['customer'])->first();
                $order->status_name = MaintenanceStatus::getStatus($order->status);
                return response()->json(['data' => $order], 200);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } else { // customer is new 
            try {
                DB::transaction(function () use (&$order, $request, $imagePaths, $validatedData) {
                    if ($request->hasFile('product_images')) {
                        foreach ($request->file('product_images') as $image) {
                            // Store the image and get its path
                            $path = $image->store('product_images', 'public');
                            $imagePaths[] = $path; // Add the path to the array
                        }
                    }
                    $customer = Customer::create([
                        'name' => $request->customerName,
                        'phone' => $request->customerPhone
                    ]);
                    $validatedData['product_images'] = json_encode($imagePaths);
                    $order = Maintenance::create([
                        'customer_id' => $customer->id,
                        'user_id' => Auth::user()->id,
                        'weight' => $request->weight,
                        'cost' => $request->cost,
                        'status' => $request->notPaid ? MaintenanceStatus::PENDINGNOTPAID : MaintenanceStatus::PENDING,
                        'product_images' => $validatedData['product_images'],
                        'notice' => 'معلومات انشاء الطلب: ' . '<br/>' . $request->notice . '<hr/>'
                    ]);
                });
                $order = Maintenance::where('id', $order->id)->with(['customer'])->first();
                $order->status_name = MaintenanceStatus::getStatus($order->status);
                return response()->json(['data' => $order], 200);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public function cancel(Request $request)
    {
        $order = Maintenance::where('id', $request->order_id)->first();
      
        $order->update([
            'status' => $order->status == MaintenanceStatus::PENDING ? MaintenanceStatus::CANCELEDPAID : MaintenanceStatus::CANCELED,
            'notice' =>  $order->notice  . 'معلومات الغاء الطلب ' . '<br>' . $request->cancelNotice . '<br>' .' المسؤول ' . Auth::user()->name . '<br> ' 
        ]);
        $order->save();
        return response()->json(['data' => $order]); 
    }

    public function recive(Request $request)
    {
        $order = Maintenance::where('id', $request->order_id)->first();

        $order->update([
            'last_cost' => $request->last_cost,
            'status' => $order->status == MaintenanceStatus::PENDINGNOTPAID ? MaintenanceStatus::RECIVED : MaintenanceStatus::RECIVEDPREPAID, // تمت الصيانة وندفع اليوم
            'notice' => 'معلومات التسليم:' . '<br>' . $request->reciveNotice . ' <br>' . ' تم التسليم من قبل ' . Auth::user()->name . '<hr>',
            'recevieved_date' => Carbon::now(),
        ]);

        $order->save();

        return response()->json(['data' => json_encode($order), 'message' => 'success'], 200);
    }
}
