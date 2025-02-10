@extends('layouts.app')

@section('content')
    <h3>تفاصيل طلب </h3>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>العميل</th>
                        <td>{{ $order->customer->name }}</td>
                    </tr>
                    <tr>
                        <th>رقم الهاتف</th>
                        <td>{{ $order->customer->phone }}</td>
                    </tr>
                    <tr>
                        <th> العربون</th>
                        <td><b>€</b> {{ $order->amount_paid }}</td>
                    </tr>
                    <tr>
                        <th>منشئ الطلب</th>
                        <td>{{ $order->user->name }}</td>
                    </tr>
                    <tr>
                        <th>الحالة</th>
                        <td>{{ $order->status_name }}</td>
                    </tr>
                    <tr>
                        <th> المنتج المرتبط </th>
                        <td>
                            @if ( $order->product_id )
                                <a href="{{ route('products.show', $order->product_id) }}">عرض</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>وزن المنتج المسلّم</th>
                        <td>{{ $order->product->weight ?? '-' }} g</td>
                    </tr>
                    <tr>
                        <th> إجمالي السعر المباع</th>
                        <td>{{ $order->product->selled_price ?? '-' }} <b>€</b></td>
                    </tr>
                    <tr>
                        <th>تاريخ التسليم</th>
                        <td>{{ $order->received_date ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>مسؤول التسليم</th>
                        <td>{{ $order->product->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>ملاحظات</th>
                        <td>{!! $order->notice  !!}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
