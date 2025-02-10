@extends('layouts.app')

@section('content')
    <div class="container-fluid ">
       <div class="row">
        <div class="col-md-6">
            <div class="border border-primary p-2">
                <div class="row mt-2 mb-4">
                    <div class="col-md-6">
                        <h6 class="d-inline">  رمز المنتج :</h6>
                        <span class="border p-1">{{ $product->short_ident }}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="d-inline"> المعرف : {{ $product->ident }}</span>
                    </div>
                </div>
                <div class="row mt-2 mb-4">
                    <div class="col-md-6">
                        <h6 class="d-inline">النوع :</h6>
                        <span class="border p-1">{{ $product->type->name }}</span>
                    </div>
                    <div class="col-md-6">
                        <h6 class="d-inline">العيار :</h6>
                        <span class="border p-1">{{ $product->caliber->name }}</span>
                    </div>
                </div>
                <div class="row mt-2 mb-4">
                    <div class="col-md-6">
                        <h6 class="d-inline">الوزن :</h6>
                        <span class="border p-1">{{ $product->weight }}</span>
                    </div>
                    <div class="col-md-6">
                        <h6 class="d-inline">القياس :</h6>
                        <span class="border p-1">{{ $product->measurement }}</span>
                    </div>
                </div> 
                <div class="row mt-2 mb-4">
                    <div class="col-md-6">
                        <h6 class="d-inline">السعر :</h6>
                        <span class="border p-1 text-black" style="font-size: 20px"> <b>€</b>{{  $product->calculateSellingPrice() }}</span>
                    </div>
                    @php
                    if ($product->status == 1) {
                        $statusText = 'مباع';
                    } elseif ($product->status == 3) {
                        $statusText = 'تالف';
                    } else {
                        $statusText = 'متوفر';
                    }
                @endphp
                    <div class="col-md-6">
                        <h6 class="d-inline">الحالة :</h6>
                        <span class="border p-1" >{{ $statusText }}</span>
                    </div>
                </div>
                <div class="row mt-2 mb-4">
                    {{-- <div class="col-md-6">
                        <h6 class="d-inline">سعر المبيع :</h6>
                        <span class="border p-1">{{ $product->selled_price ?? '-' }}</span>
                    </div> --}}
                    <div class="col-md-6">
                        <h6 class="d-inline">تاريخ المبيع :</h6>
                        <span class="border p-1">{{ $product->selled_date ?? '-' }}</span>
                    </div>
                </div>
                <div class="row mt-2 mb-4">
                    {{-- <div class="col-md-6">
                        <h6 class="d-inline">سعر الغرام عند المبيع :</h6>
                        <span class="border p-1">{{ $product->caliber_selled_price ?? '-' }}</span>
                    </div> --}}
                </div>
                <div class="row mt-2 mb-4">
                    <div class="col-md-6">
                        <h6 class="d-inline">تاريخ انشاء السجل</h6>
                        <span class="border p-1">{{ $product->created_at }}</span>
                    </div>
                    <div class="col-md-6">
                        <h6 class="d-inline">تاريخ اخر تعديل :</h6>
                        <span class="border p-1">{{ $product->updated_at }}</span>
                    </div>
                </div>
                {{-- <div class="row mt-2 mb-4">
                    
                    <div class="col-md-6">
                        <h6 class="d-inline">البائع :</h6>
                        <span class="border p-1">{{ $product->user->name ?? '-' }}</span>
                    </div>
                </div> --}}
                <div class="row mt-2">
                    <div class="col-md-12">
                        <h6 class="d-inline">الوصف :  </h6>
                        <span class="border p-1">{{ $product->description ?? '-' }}</span>
                    </div>
                </div>
            </div>
            </div>
        </div>
       </div>
    </div>
@endsection
