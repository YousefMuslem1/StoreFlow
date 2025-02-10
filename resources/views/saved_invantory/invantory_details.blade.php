@extends('layouts.app')

@section('content')

<div class="container">
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>المعرّف</th>
                    <th>النوع</th>
                    <th>العيار</th>
                    <th>الوزن</th>
                    <th>حالة الجرد</th>
                    <th>تاريخ الادخال</th>
                    <th>العمليات</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($invantoryDetialsItems as $invantory)
                    <tr>
                        <td>{{ $invantory->product->ident }}</td>
                        <td>{{ $invantory->type->name ?? 'الكل' }}</td>
                        <td>{{ $invantory->caliber->name ?? 'الكل' }}</td>
                        <td>{{ $invantory->product->weight }}</td>
                        <td class="text-{{ $invantory->status == 2 ? 'success' : ($invantory->status == 1 ? 'orange' : 'danger') }}">{{ $invantory->status == 2 ? 'متوفر' : ($invantory->status == 1 ? 'مباع' : 'غير متوفر') }}</td>


                        <td>{{ $invantory->product->created_at }}</td>
                        <td><a href="{{ route('products.show', $invantory->product->id) }}" target="_blank"
                                class="btn  btn-primary text-white btn-sm">تفاصيل المنتج</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $invantoryDetialsItems->links() }}
    </div>
</div>
@endsection