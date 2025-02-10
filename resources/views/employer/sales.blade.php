@extends('employer.layouts.app')
@section('styles')
    <style>
        .table-container {
            overflow-x: auto;
        }

        @media (max-width: 576px) {
            #submitButton {
                margin-bottom: 20px;
                /* Adjust the margin-top value as needed */
            }
        }
    </style>
@endsection
@section('content')
    <a href="/sell" class="btn btn-secondary mt-2">رجوع</a>
    <hr>
    <h3>المبيعات اليوم</h3>
    <div class="table-container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رمز المنتج</th>
                    <th>النوع</th>
                    <th>الصنف</th>
                    <th>الوزن</th>
                    <th>سعر المبيع</th>
                    <th>سعر الغرام</th>
                    {{-- <th>تاريخ البيع</th> --}}
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product->short_ident ?? 'كمية' }}</td>
                        <td>{{ $product->caliber->full_name }}</td>
                        <td>{{ $product->type->name }}</td>
                        <td>{{ $product->weight }}</td>
                        <td>{{ $product->selled_price }}</td>
                        <td>{{ number_format($product->selled_price / $product->weight, 2) }}</td>
                        {{-- <td>{{ $product->selled_date }}</td> --}}
                        @if (Auth::user()->id == $product->user_id && $product->short_ident)
                           <form action="{{ route('sell.cancel', $product->id) }}" method="POST">
                            @csrf
                            <td><button type="submit" class="btn btn-danger btn-sm">الغاء </button></td>
                        </form>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <hr>
    <h3>أوزان جديدة</h3>
    <div class="table-container">
        <table class="table">
            <thead>
                <th>#</th>
                <th>المنتج</th>
                <th>العيار</th>
                <th>الصنف</th>
                <th>الوزن</th>
            </thead>
            <tbody>
                @foreach ($newProducts as $selled_product)
                    <tr class="text-{{ !empty($selled_product->description) ? 'success' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $selled_product->short_ident }}</td>
                        <td>{{ $selled_product->caliber->full_name }}</td>
                        <td>{{ $selled_product->type->name }}</td>
                        <td>{{ $selled_product->weight }}</td>
                        </td>
                    </tr>
                @endforeach
                @foreach ($newQuantities as $quantity)
                    @foreach ($quantity->weightQuantities as $weightQuantity)
                        <tr class="text-{{ !empty($selled_product->description) ? 'success' : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $quantity->short_ident }}</td>
                            <td>{{ $quantity->caliber->full_name }}</td>
                            <td>{{ $quantity->type->name }}</td>
                            <td>{{ $weightQuantity->weight }} g</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

