<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\Order;
use App\Models\Caliber;
use App\Models\Customer;
use App\Models\Payment;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use Illuminate\Http\Request;
use function PHPSTORM_META\map;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve search parameters
        $search = $request->input('search');
        $status = $request->input('status');
        // Base query
        $query = Order::with(['customer', 'product', 'user']);

        // Apply filters based on the search parameters
        if ($search) {
            $query->whereHas('customer', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }


        // Fetch orders with pagination
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        foreach ($orders as $order) {
            $order->status_name = OrderStatus::getStatus($order->status);
        }
        // Fetch all calibers and types for the filter dropdowns
        $calibers = Caliber::all();
        $types = Type::all();
        $statusOptions = [
            '' => 'All',
            OrderStatus::PENDING => 'معلّق',
            OrderStatus::RECIVED => 'تم التسليم',
            OrderStatus::CANCELED => 'ملغي',
            OrderStatus::AVAILABLEPRODUCT => 'محجوز'
        ];
        return view('employer.orderes.index', compact('orders', 'calibers', 'types', 'statusOptions'));
    }



    public function create()
    {
        return view('employer.orderes.create');
    }

    public function store(Request $request)
    {
        $customer = $request->customer_name;
        $phone = $request->customer_phone;
        $customer_id = $request->customer_id;
        $amount_paid = $request->amount_paid;
        $notice = $request->notice;
        $product_number = $request->product_number;
        $productIsSet = $request->productIsSet;
        $selled_price = $request->proudct_selled_price;
        $request->validate([
            'customer_name' => 'required',
            'amount_paid' => 'required',
        ]);
        //customer is exist
        if ($customer_id) {
            $customer = Customer::where('id', $customer_id)->first();
            try {
                DB::transaction(function () use (&$order, $customer, $amount_paid, $notice, $productIsSet, $product_number, $selled_price) {
                    $order =  Order::create([
                        'customer_id' => $customer->id,
                        'user_id' => Auth::user()->id,
                        'amount_paid' => $amount_paid,
                        'notice' => $notice,
                        // 'product_id' => $productIsSet ? $product_number : null,
                        'status' => $productIsSet ? OrderStatus::AVAILABLEPRODUCT : OrderStatus::PENDING,
                    ]);

                    if ($amount_paid > 0) {
                        Payment::create([
                            'order_id' => $order->id,
                            'amount' => $amount_paid,
                            'status' => OrderStatus::PENDING,
                        ]);
                    }

                    if ($productIsSet) {
                        $product = Product::where('short_ident', $product_number)->first();

                        $product->update([
                            'selled_price' => $selled_price,
                            'user_id' => Auth::user()->id,
                            'status' => ProductStatus::BOOKED,
                            'selled_date' => Carbon::now(),
                            'description' => $product->description . ' -----  ' . $notice
                        ]);
                        $order->product_id = $product->id;
                        $order->save();
                        $product->save();
                    }
                });
                $order = Order::where('id', $order->id)->with(['customer', 'user', 'product'])->first();
                $order->status_name = OrderStatus::getStatus($order->status);

                // Create a message for Telegram
                $message = "-------- عربون جديد ----------"
                    . "\n العميل: " . $customer->name
                    . "\nنوع العربون: " . ($productIsSet ? "حجز منتج" : "توصاي")
                    . "\n المبلغ المدفوع : " . $order->amount_paid . " €"
                    . "\n ملاحظات : " . $order->notice . "\n";
                // If the product is set, add the product details to the message
                if ($productIsSet) {
                    $product = Product::where('short_ident', $product_number)->first();

                    $message .= "\n------تفاصيل المنتج -------"
                        . "\nرمز المنتج: " . $product->short_ident
                        . "\nسعر المبيع: " . $product->selled_price . " €"
                        . "\nالوزن: " . $product->weight . ' g'
                        . "\n سعر الغرام: " . number_format($product->selled_price / $product->weight, 2) . " €"
                        . "\nالصنف: " . $product->type->name
                        . "\nالعيار: " . $product->caliber->full_name
                        . "\nالبائع: " . $product->user->name
                        . "\nملاحظات: " . ($product->description ?? 'لايوجد');
                }
                sendTelegramMessage($message);

                return redirect()->back()->with(['order_added' => $order]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        } else { // new customer 

            try {
                DB::transaction(function () use (&$order, $customer, $phone,  $amount_paid, $notice, $productIsSet, $product_number, $selled_price) {
                    $customer = Customer::create([
                        'name' => $customer,
                        'phone' => $phone,
                    ]);
                    $order =   Order::create([
                        'customer_id' => $customer->id,
                        'user_id' => Auth::user()->id,
                        'amount_paid' => $amount_paid,
                        // 'product_id' => $productIsSet ? $product_number : null,
                        'notice' => $notice,
                        'status' => $productIsSet ? OrderStatus::AVAILABLEPRODUCT : OrderStatus::PENDING,
                    ]);
                    if ($amount_paid > 0) {
                        Payment::create([
                            'order_id' => $order->id,
                            'amount' => $amount_paid,
                            'status' => OrderStatus::PENDING,
                        ]);
                    }
                    if ($productIsSet) {
                        $product = Product::where('short_ident', $product_number)->first();
                        $product->update([
                            'selled_price' => $selled_price,
                            'user_id' => Auth::user()->id,
                            'status' => ProductStatus::BOOKED,
                            'selled_date' => Carbon::now(),
                            'description' => $product->description . ' -----  ' . $notice
                        ]);
                        $order->product_id = $product->id;
                        $order->save();
                        $product->save();
                    }
                    $message = "-------- عربون جديد ----------"
                        . "\n العميل: " . $customer->name
                        . "\nنوع العربون: " . ($productIsSet ? "حجز منتج" : "توصاي")
                        . "\n المبلغ المدفوع : " . $order->amount_paid . " €"
                        . "\n ملاحظات : " . $order->notice . "\n";
                    // If the product is set, add the product details to the message
                    if ($productIsSet) {
                        $product = Product::where('short_ident', $product_number)->first();

                        $message .= "\n------تفاصيل المنتج -------"
                            . "\nرمز المنتج: " . $product->short_ident
                            . "\nسعر المبيع: " . $product->selled_price . " €"
                            . "\nالوزن: " . $product->weight . ' g'
                            . "\n سعر الغرام: " . number_format($product->selled_price / $product->weight, 2) . " €"
                            . "\nالصنف: " . $product->type->name
                            . "\nالعيار: " . $product->caliber->full_name
                            . "\nالبائع: " . $product->user->name
                            . "\nملاحظات: " . ($product->description ?? 'لايوجد');
                    }
                    sendTelegramMessage($message);
                });

                $order = Order::where('id', $order->id)->with(['customer', 'user', 'product'])->first();
                $order->status_name = OrderStatus::getStatus($order->status);
                return redirect()->back()->with(['order_added' => $order]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    public function cancelOrder(Request $request)
    {
        try {
            DB::transaction(function () use ($request, &$order) {
                // Fetch the order with its related customer and product
                $order = Order::with('customer', 'product', 'payments')->findOrFail($request->orderId);

                // Update the order status and notice
                $order->status = OrderStatus::CANCELED;
                $order->notice .= '<hr />' . $request->cancel_reason . '<hr />' . ' -----معلومات النظام----- <hr />' . 'الغاء الطلب من قبل ' . Auth::user()->name;
                $order->save();

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $order->payments->sum('amount'),
                    'status' => OrderStatus::CANCELED,
                ]);

                // Delete the related product if it exists
                if ($order->product) {
                    $order->product->update([
                        'selled_price' => null,
                        'selled_date' => null,
                        'user_id' => null,
                        'status' => ProductStatus::AVAILABLE,
                        'description' => $order->product->description . ' تم الغاء الحجز من قبل ' . Auth::user()->name
                    ]);
                }

                // Filter notice for Telegram
                $filteredNotice = $this->filterHtmlForTelegram($order->notice);
                // Create a message for Telegram
                $message = "-------- عربون ملغي ----------"
                    . "\n العميل: " . $order->customer->name
                    . "\nنوع العربون: " . ($order->product ? "حجز منتج" : "توصاي")
                    . "\n المبلغ المدفوع : " . $order->amount_paid . " €"
                    . "\n ملاحظات : " . $filteredNotice . "\n";
                // If the product is set, add the product details to the message
                if ($order->product) {
                    $product = $order->product;
                    $message .= "\n------تفاصيل المنتج -------"
                        . "\nرمز المنتج: " . $order->product->short_ident
                        // . "\nسعر المبيع: " . $order->product->selled_price . " €"
                        . "\nالوزن: " . $order->product->weight . ' g'
                        // . "\n سعر الغرام: " . number_format($product->selled_price / $product->weight, 2) . " €"
                        . "\nالصنف: " . $order->product->type->name
                        . "\nالعيار: " . $order->product->caliber->full_name
                        // . "\المسؤول: " . $product->user->name
                        . "\nملاحظات: " . ($order->product->description ?? 'لايوجد');
                }
                sendTelegramMessage($message);
            });

            return redirect()->back()->with(['message' => 'تم الغاء طلب العميل ' . $order->customer->name . ' بنجاح']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function deliveredOrder(Request $request)
    {
        if (!$request->booked) {
            $request->validate([
                'weight' => 'required',
                'caliber' => 'required',
                'type' => 'required',
                'selled_price' => 'required',
            ]);
        }

        try {
            $order = Order::with(['customer', 'payments'])->where('id', $request->order_id)->first();
            DB::transaction(function () use ($request, &$order) {
                if (!$request->booked) {
                    $product =  Product::create([
                        'weight' => $request->weight,
                        'description' => $request->notice  . '' . ' تم تسليم المنتج من قبل ' . Auth::user()->name . '  للعميل ' . $order->customer->name . ' مدفوع سابقاً ' . $order->amount_paid . ' €',
                        'selled_price' => $request->selled_price,
                        'status' => ProductStatus::ORDER,
                        'selled_date' => Carbon::now(),
                        'user_id' => Auth::user()->id,
                        'caliber_id' => $request->caliber,
                        'type_id' => $request->type
                    ]);
                    $new_payment =  Payment::create([
                        'order_id' => $order->id,
                        'amount' => $request->selled_price - $order->payments->sum('amount'),
                        'status' => OrderStatus::RECIVED,
                    ]);
                    // Filter notice for Telegram
                    $filteredNotice = $this->filterHtmlForTelegram($order->notice);
                    $message = "-------- عربون تم التسليم ----------"
                        . "\n العميل: " . $order->customer->name
                        . "\nنوع العربون: " . "توصاي"
                        . "\n المبلغ المدفوع : " . $order->amount_paid . " €"
                        . "\n دفعة الجديدة : " . $new_payment->amount . " €"
                        . "\n ملاحظات : " . $filteredNotice . "\n";
                    // If the product is set, add the product details to the message
                    $message .= "\n------تفاصيل المنتج -------"
                        // . "\nرمز المنتج: " . $order->product->short_ident
                        . "\nسعر المبيع: " . $product->selled_price . " €"
                        . "\nالوزن: " . $product->weight . ' g'
                        . "\n سعر الغرام: " . number_format($product->selled_price / $product->weight, 2) . " €"
                        . "\nالصنف: " . $product->type->name
                        . "\nالعيار: " . $product->caliber->full_name
                        . "\nالمسؤول: " . $product->user->name
                        . "\nملاحظات: " . ($product->description ?? 'لايوجد');
                    sendTelegramMessage($message);
                } else {
                    $product = Product::where('id', $order->product_id)->first();
                    $product->description .= ' -----' . $request->notice;
                    $product->status = ProductStatus::SOLD;
                    $product->user_id = Auth::user()->id;

                    $product->save();

                    $new_payment = Payment::create([
                        'order_id' => $order->id,
                        'amount' => $product->selled_price - $order->amount_paid,
                        'status' => OrderStatus::RECIVED,
                    ]);
                    $filteredNotice = $this->filterHtmlForTelegram($order->notice);

                    $message = "-------- عربون تم التسليم ----------"
                        . "\n العميل: " . $order->customer->name
                        . "\nنوع العربون: " . ($order->product ? "حجز منتج" : "توصاي")
                        . "\n المبلغ المدفوع : " . $order->amount_paid . " €"
                        . "\n دفعة الجديدة : " . $new_payment->amount . " €"
                        . "\n ملاحظات : " . $filteredNotice . "\n";
                    // If the product is set, add the product details to the message
                    $message .= "\n------تفاصيل المنتج -------"
                    
                        . "\nرمز المنتج: " . $order->product->short_ident
                        . "\nسعر المبيع: " . $order->product->selled_price . " €"
                        . "\nالوزن: " . $order->product->weight . ' g'
                        . "\n سعر الغرام: " . number_format($product->selled_price / $product->weight, 2) . " €"
                        . "\nالصنف: " . $order->product->type->name
                        . "\nالعيار: " . $order->product->caliber->full_name
                        . "\المسؤول: " . $product->user->name
                        . "\nملاحظات: " . ($order->product->description ?? 'لايوجد');
                }
                sendTelegramMessage($message);

                $order->update([
                    'status' => OrderStatus::RECIVED,
                    'received_date' => Carbon::now(),
                    'notice' => $order->notice . '<hr />' . $request->notice . ' <hr />' . ' تم تسليم المنتج من قبل ' . Auth::user()->name . ' --> الوزن النهائي ' . $request->weight . ' سعر المبيع ' . $request->selled_price . ' euro ',
                    'product_id' => $product->id
                ]);
                $order->save();

            });
            return redirect()->back()->with(['order_delivered' => $order]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function show(Request $request)
    {
        $order = Order::with(['customer', 'user', 'product.user'])->where('id', $request->order)->first();
        // Add status name to each order
        $order->status_name = \App\Enums\OrderStatus::getStatus($order->status);
        return view('admin_orders.show', compact('order'));
    }

    // Function to filter the HTML for Telegram
    function filterHtmlForTelegram($text)
    {
        // Strip all HTML tags except those supported by Telegram
        $text = strip_tags($text, '<b><i><u><a><code><pre>');

        // Escape special characters
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        return $text;
    }
}
