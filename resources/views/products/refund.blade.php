@extends('layouts.app')

@section('content')
    <div class="container">
        @if ($new_ident !== null)
            <div class="alert alert-success">
                تم التعديل بنجاح!

            </div>
        @endif
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <table class="table table-bordered">
                    <thead>
                    </thead>
                    <tbody>
                        <tr>
                            <td>اسم المنتج </td>
                            <td>{{ $product->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>نوع المنتج </td>
                            <td>{{ $product->type->name }}</td>
                        </tr>
                        <tr>
                            <td> العيار </td>
                            <td>{{ $product->caliber->full_name }}</td>
                        </tr>
                        <tr>
                            <td>السعر</td>
                            <td>{{ $product->calculateSellingPrice() }} €</td>
                        </tr>
                        <tr>
                            <td>الوزن</td>
                            <td>{{ $product->weight . ' gr' }} </td>
                        </tr>
                        <tr>
                            <td>الحالة</td>
                            <td>{{ $product->status == 1 ? 'مباع' : 'متوفر' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <form action="{{ route('product_reset') }}" method="post">
            @csrf
            <input type="hidden" name="product" value="{{ request()->segment(3) }}">
            <button type="submit" class="btn btn-danger"> إعادة ضبط مع الحفاظ على الرمز القديم</button>
        </form>
        <hr>
        <h3>إدخال معرّف جديد ورمز جديد</h3>
        <hr>
        <div class="row">
            <div class="col-sm-3 col-md-2">
                <label for="">رمز المنتج</label>
                <h5>{{ $short_ident }}</h4>
            </div>
            <div class="col-sm-7 col-md-4">
                <form action="{{ route('products.refund_update', $product->id) }}" method="post">
                    @csrf
                    @method('post')
                    <div class="form-group">
                        <label for="ident">رقم المعرَف</label>
                        <input type="text" value="{{ $new_ident }}" class="form-control {{ $errors->has('ident') ? ' is-invalid' : '' }}" name="ident" required
                            autofocus autocomplete="off">
                        @error('ident')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="short_ident" value="{{ $short_ident }}">
                    </div>
                    <button class="btn btn-success" type="submit">حفظ</button>
                </form>
            </div>
        </div>
    </div>
@endsection
