@extends('layouts.app')

@section('styles')
    <style>
        .table-container {
            overflow-x: auto;
        }
    </style>
@endsection
@section('content')
    <form action="{{ route('reports.result') }}" method="POST">
        @csrf
        <div class="row">

            <div class="col-sm-3">
                <div class="form-group">
                    <label for="from">من</label>
                    <input type="date" class="form-control" name="from" value="{{ old('from') }}">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label for="to">الى</label>
                    <input type="date" class="form-control" name="to" value="{{ old('to') }}">
                </div>
            </div>
            <div class="col-sm-1">
                <div class="form-group">
                    <label for=""></label>
                    <button type="submit" class="mt-1 btn btn-success form-control">تنفيذ</button>
                </div>
            </div>
        </div>
    </form>

    @if (session('setResult'))
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th> منتجات مباعة (ستيكر) </th>
                        <th>الوزن المباع 24</th>
                        <th>الوزن المباع كمية</th>
                        <th>الوزن المباع كمية كـ 24</th>
                        <th class="text-warning">الوزن الاجمالي المباع</th>
                        <th class="text-warning">الوزن الاجمالي المباع كـ24</th>
                        <th>اوزان مشتراة</th>
                        <th>اوزان مشتراة كـ24</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-success">
                        <td>{{ session('totalWeightbeforeTransforming') }} g</td>
                        <td>{{ session('products_weights_as_fine') }} g</td>
                        <td>{{ session('quantities_as_total') }} g</td>
                        <td>{{ session('quantities_as_fine') }} g</td>

                        <td class="text-danger">
                            {{ session('quantities_as_total') + session('totalWeightbeforeTransforming') }} g</td>
                        <td class="text-danger">{{ session('products_weights_as_fine') + session('quantities_as_fine') }} g
                        </td>

                        <td>{{ session('quantities_as_total_bought') }} g</td>
                        <td>{{ session('quantities_as_fine_bought') }} g</td>
                    </tr>
                </tbody>
            </table>
        </div>


        <hr>


        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>قيمة المبيعات للمنتجات (ستيكر)</th>
                        <th>قيمة مبيعات الكمية</th>
                        <th class="text-warning">القيمة الاجمالية للمبيعات</th>
                        <th>ديون متبقية</th>
                        <th>القيمة الاجمالية لشراء</th>
                    </tr>
                </thead>

                <tbody>
                    <tr class="table-danger">
                        <td>{{ session('total_selled_product_price') }} €</td>
                        <td> {{ session('quantities_selled_total') }} €</td>
                        <td class="text-danger">
                            {{ session('total_selled_product_price') + session('quantities_selled_total') }} €</td>
                        <td>{{ session('quantitityInstallmentRemaining') + session('productsInstallmentRemaining') }} €
                        </td>
                        <td>{{ session('totalBoughtPrice') }} €</td>
                    </tr>


                </tbody>
            </table>

        </div>


        <h3>صندوق العربون</h3>
        <table class="table table-bordered">
            <thead>
                <tr class="table-primary">
                    <th>نوع العملية</th>
                    <th>إجمالي المبلغ (€)</th>
                    <th>عدد العمليات</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $status = ['', 'عربون جديد', 'تم تسليم الطلب', 'ملغي'];
                    $totalOrderBox = 0;
                @endphp
                @foreach (session('orders_total') as $group)
                    <tr>
                        <td>{{ $status[$group->status] }}</td>
                        <td class="text-{{ $group->status == 3 ? 'danger' : 'success' }}">
                            {{ number_format($group->total_amount, 2) ?? 0 }} €</td>
                        <td>{{ $group->payment_count ?? 0 }}</td>
                        @if ($group->status == 3)
                            @php
                                $totalOrderBox += $group->total_amount * -1;
                            @endphp
                        @else
                            @php
                                $totalOrderBox += $group->total_amount;
                            @endphp
                        @endif
                    </tr>
                @endforeach
                <tr class="table-primary">
                    <td colspan="1">إجمالي صندوق العربون</td>
                    <td colspan="2">{{ $totalOrderBox }} €</td>
                </tr>
            </tbody>
        </table>
    @endif
@endsection
