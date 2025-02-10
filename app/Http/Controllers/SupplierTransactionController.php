<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierBalance;
use Illuminate\Support\Facades\DB;
use App\Models\SupplierTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SupplierTransactionController extends Controller
{
    public function storeTransaction(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'type' => 'required|in:1,2',
            'transaction_type' => 'required|in:1,2', // 1 لإرسال, 2 لاستقبال
            'amount' => 'required|numeric',
            'price_per_gram' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            if ($request->type == 1) { // العملية هي مال
                if ($request->transaction_type == 1) { // إرسال
                    $this->handleSendMoney($request);
                } elseif ($request->transaction_type == 2) { // استلام
                    $this->handleReceiveMoney($request);
                }
            } elseif ($request->type == 2) { // العملية هي ذهب
                if ($request->transaction_type == 1) { // إرسال
                    $this->handleSendGold($request);
                } elseif ($request->transaction_type == 2) { // استلام
                    $this->handleReceiveGold($request);
                }
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function handleSendMoney($request)
    {
        // جعل المبلغ سالبًا لأن العملية إرسال
        $amount = -abs($request->amount);
        $expectedWeight = null;

        // حساب الوزن المتوقع إذا كان سعر التثبيت موجودًا
        if ($request->price_per_gram) {
            $expectedWeight = abs($amount) / $request->price_per_gram;
        }

        // إنشاء العملية
        $transaction = SupplierTransaction::create([
            'supplier_id' => $request->supplier_id,
            'type' => $request->type,
            'amount' => $amount,
            'price_per_gram' => $request->price_per_gram,
            'user_id' => Auth::id(),
            'expected_weight' => $expectedWeight,
            'status' => 1, // تحديد الحالة على أنها معلقة
        ]);

        // تحديث الرصيد
        $supplierBalance = SupplierBalance::firstOrCreate(['supplier_id' => $request->supplier_id]);

        if ($request->type == 1) { // العملية هي مال
            if ($request->price_per_gram) {
                // التحقق من `fixed_gold` الحالي
                if ($supplierBalance->fixed_gold > 0) {
                    // إذا كان لدينا ذهب مثبت موجبة، استخدمه لتغطية الوزن المتوقع
                    $weightToUse = min($supplierBalance->fixed_gold, $expectedWeight);
                    $transaction->update([
                        'received_weight' => $weightToUse
                    ]);

                    // تحديث `fixed_gold` بعد استخدام الذهب
                    $supplierBalance->fixed_gold -= $weightToUse;
                    $expectedWeight -= $weightToUse;

                    // إذا كان الوزن المتوقع المتبقي هو صفر، فاجعل العملية مكتملة
                    if ($expectedWeight == 0) {
                        $transaction->update(['status' => 2]); // مكتمل
                    } else {
                        // طرح الوزن المتبقي من `fixed_gold`
                        $supplierBalance->fixed_gold -= $expectedWeight;
                    }
                } else {
                    // طرح الوزن المتوقع من `fixed_gold` ليعكس أننا ندين بهذا الوزن
                    $supplierBalance->fixed_gold -= $expectedWeight;
                }

                // تحديث المال المثبت
                $supplierBalance->fixed_money += $amount;
            } else {
                $supplierBalance->unfixed_money += $amount;
            }
        }

        $supplierBalance->save();
    }





    private function handleReceiveMoney($request)
    {
        // جعل المبلغ موجب لأن العملية استقبال
        $amount = abs($request->amount);
        $expectedWeight = null;

        if ($request->type == 1 && $request->price_per_gram) {
            // للمال مع سعر الذهب المثبت، حساب الوزن المتوقع
            $expectedWeight = $amount / $request->price_per_gram;
        }

        // إنشاء العملية
        $transaction = SupplierTransaction::create([
            'supplier_id' => $request->supplier_id,
            'type' => $request->type,
            'amount' => $amount,
            'price_per_gram' => $request->price_per_gram,
            'user_id' => Auth::id(),
            'expected_weight' => $expectedWeight,
            'status' => 1, // تحديد الحالة على أنها معلقة
        ]);

        // تحديث الرصيد
        $supplierBalance = SupplierBalance::firstOrCreate(['supplier_id' => $request->supplier_id]);

        if ($request->type == 1) { // العملية هي مال
            if ($request->price_per_gram) {
                $supplierBalance->fixed_money += $amount;
                $supplierBalance->fixed_gold -= $expectedWeight;
            } else {
                $supplierBalance->unfixed_money += $amount;
            }
        }

        $supplierBalance->save();
    }

    private function handleSendGold($request)
    {
        // جعل الكمية سالبة لأن العملية إرسال
        $amount = -abs($request->amount);
        $expectedWeight = null;

        if ($request->price_per_gram) {
            // حساب الوزن المتوقع
            $expectedWeight = abs($amount) / $request->price_per_gram;
        }

        // إنشاء العملية
        $transaction = SupplierTransaction::create([
            'supplier_id' => $request->supplier_id,
            'type' => $request->type,
            'amount' => $amount,
            'price_per_gram' => $request->price_per_gram,
            'user_id' => Auth::id(),
            'expected_weight' => $expectedWeight,
            'status' => 1, // تحديد الحالة على أنها معلقة
        ]);

        // تحديث الرصيد
        $supplierBalance = SupplierBalance::firstOrCreate(['supplier_id' => $request->supplier_id]);

        if ($request->price_per_gram) {
            $supplierBalance->fixed_gold += $amount; // تحديث الذهب المثبت
        } else {
            $supplierBalance->unfixed_gold += $amount; // تحديث الذهب الغير مثبت
        }

        $supplierBalance->save();
    }

    private function handleReceiveGold($request)
    {
        // جعل الكمية موجبة لأن العملية استقبال
        $amount = abs($request->amount);
        $expectedWeight = null;

        if ($request->price_per_gram) {
            // حساب الوزن المتوقع
            $expectedWeight = $amount / $request->price_per_gram;
        }

        // إنشاء العملية
        $transaction = SupplierTransaction::create([
            'supplier_id' => $request->supplier_id,
            'type' => $request->type,
            'amount' => $amount,
            'price_per_gram' => $request->price_per_gram,
            'user_id' => Auth::id(),
            'expected_weight' => $expectedWeight,
            'status' => $request->price_per_gram ? 2 : 1, // تحديد الحالة بناءً على سعر التثبيت
        ]);

        // تحديث الرصيد
        $supplierBalance = SupplierBalance::firstOrCreate(['supplier_id' => $request->supplier_id]);

        if ($request->price_per_gram) {
            $totalWeight = $amount; // الوزن المستلم بالكامل
            $remainingWeight = $totalWeight; // الوزن المتبقي بعد التوزيع

            // توزيع الذهب المستلم على المعاملات المعلقة التي تم إرسال المال فيها
            $pendingTransactions = SupplierTransaction::where('supplier_id', $request->supplier_id)
                ->where('type', 1) // معاملات المال
                ->where('status', 1) // حالة معلقة
                ->whereNull('received_weight') // لم يتم استلام الذهب بعد
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($pendingTransactions as $pendingTransaction) {
                $requiredWeight = $pendingTransaction->expected_weight;

                if ($remainingWeight >= $requiredWeight) {
                    // تحديث المعاملة مع الوزن المستلم بالكامل
                    $pendingTransaction->update([
                        'received_weight' => $requiredWeight,
                        'status' => 2 // تحديث الحالة إلى مكتمل
                    ]);

                    // تقليل الوزن المتبقي
                    $remainingWeight -= $requiredWeight;

                    // تقليل الوزن المستلم من fixed_gold
                    $supplierBalance->fixed_gold += $requiredWeight; // قم بإضافة الوزن بدلاً من الطرح
                } else {
                    // إذا كان الوزن المتبقي أقل من المطلوب، نحدث المعاملة بقدر الوزن المتبقي فقط
                    $pendingTransaction->update([
                        'received_weight' => $remainingWeight
                    ]);

                    // تقليل الوزن المستلم من fixed_gold
                    $supplierBalance->fixed_gold += $remainingWeight; // قم بإضافة الوزن بدلاً من الطرح

                    // إيقاف عملية التوزيع لأن الوزن المتبقي أصبح صفرًا
                    $remainingWeight = 0;
                    break;
                }
            }

            // إذا كان هناك أي وزن متبقي، يجب أن يتم إضافته إلى الذهب المثبت
            if ($remainingWeight > 0) {
                $supplierBalance->fixed_gold += $remainingWeight;
            }

            // تحديث المال المثبت بناءً على الوزن المستلم وسعر التثبيت
            $fixedMoneyValue = $amount * $request->price_per_gram;
            $supplierBalance->fixed_money += $fixedMoneyValue;
        } else {
            // تحديث الذهب الغير مثبت
            $supplierBalance->unfixed_gold += $amount;
        }

        $supplierBalance->save();
    }








    public function fixPrice(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|exists:supplier_transactions,id',
            'price_per_gram' => 'required|numeric|min:0',
        ]);

        $transaction = SupplierTransaction::find($request->transaction_id);

        // تحديث العملية بالسعر الجديد
        $transaction->price_per_gram = $request->price_per_gram;

        // حساب الوزن المتوقع بناءً على السعر المثبت
        if ($transaction->amount && $transaction->price_per_gram) {
            $expectedWeight = abs($transaction->amount) / $transaction->price_per_gram;
            $transaction->expected_weight = $expectedWeight;
        }

        $transaction->save();

        // تحديث الأرصدة في supplier_balances
        $supplierBalance = $transaction->supplier->supplierBalance;

        // نقل المال من unfixed_money إلى fixed_money
        $supplierBalance->unfixed_money += abs($transaction->amount);
        $supplierBalance->fixed_money -= $transaction->amount * -1;

        // تحديث الوزن المثبت
        $supplierBalance->fixed_gold += -$transaction->expected_weight;

        $supplierBalance->save();

        return response()->json(['success' => true]);
    }
}
