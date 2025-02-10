@extends('employer.layouts.app')


@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">تفاصيل المنتج</h1>
    </div>
    <hr>

    <div class="card mb-4">
        <div class="card-header">
            <h4>تفاصيل المنتج</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>رقم التعريف</th>
                        <td>{{ $product->ident }}</td>
                    </tr>
                    <tr>
                        <th>رمز المنتج </th>
                        <td>{{ $product->short_ident }}</td>
                    </tr>
                    <tr>
                        <th>الوزن</th>
                        <td>{{ $product->weight }} g</td>
                    </tr>
                    <tr class="table-secondary">
                        <th>العميل</th>
                        <td>{{ $product->order->customer->name }} </td>
                    </tr>
                    <tr  class="table-primary">
                        <th>سعر المبيع</th>
                        <td>{{ $product->selled_price }} €</td>
                    </tr>
                     <tr class="table-danger">
                        <th>مجموع المدفوعات</th>
                        <td>{{ $amountPaidSum }} €</td>
                    </tr>

                    <tr class="table-info">
                        <th> المبلغ المتبقي</th>
                        <td>{{  $product->selled_price - $amountPaidSum }} €</td>
                    </tr>
                    <tr>
                        <th>الحالة</th>
                        <td>{{ $product->status_name }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ البيع</th>
                        <td>{{ $product->selled_date }}</td>
                    </tr>
                    <tr>
                        <th>المستخدم</th>
                        <td>{{ $product->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>العيار</th>
                        <td>{{ $product->caliber->full_name }}</td>
                    </tr>
                    <tr>
                        <th>النوع</th>
                        <td>{{ $product->type->name }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ الإنشاء</th>
                        <td>{{ $product->created_at }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ التحديث</th>
                        <td>{{ $product->updated_at }}</td>
                    </tr>
                    <tr>
                        <th>الوصف</th>
                        <td>{{ $product->description }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('orderes.index') }}" class="btn btn-secondary">رجوع إلى القائمة</a>
@endsection
