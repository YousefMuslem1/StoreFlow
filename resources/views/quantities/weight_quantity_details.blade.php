@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-4">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td> نوع الكمية</td>
                        <td>{{ $weightQuantity->quantity->type->name }}</td>
                    </tr>
                    <tr>
                        <td>العيار</td>
                        <td>{{ $weightQuantity->quantity->caliber->full_name }}</td>
                    </tr>
                    <tr>
                        <td>نوع العملية</td>
                        <td>{{ getStatusName($weightQuantity->status) }}</td>
                    </tr>
                    <tr>
                        <td>سعر التحويل</td>
                        <td>{{ $weightQuantity->ounce_price ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td> الوزن</td>
                        <td class="text-{{$weightQuantity->weight > 0 ? 'success' : 'danger'  }}">{{ $weightQuantity->weight }}</td>
                    </tr>
                    <tr>
                        <td> السعر</td>
                        <td>{{ $weightQuantity->price ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>المنتج المرتبط</td>
                        @if($weightQuantity->product_id)
                        <td>
                            <a href="{{Auth::user()->type == 1 ? route('products.show', $weightQuantity->product_id) : route('dataentry.show', $weightQuantity->product_id) }}" target="_blank">تفاصيل</a>
                        </td>
                    @endif
                    
                    </tr>
                    <tr>
                        <td> المدخل</td>
                        <td>{{ $weightQuantity->user->name  }}</td>
                    </tr>
                    <tr>
                        <td> تاريخ الانشاء</td>
                        <td>{{ $weightQuantity->created_at }}</td>
                    </tr>
                    <tr>
                        <td> تاريخ آخر تعديل</td>
                        <td>{{ $weightQuantity->updated_at }}</td>
                    </tr>
                    
                    <tr>
                        <td> ملاحظات</td>
                        <td>{{ $weightQuantity->notice }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
