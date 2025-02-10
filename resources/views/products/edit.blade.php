@extends('layouts.app')

@section('content')
    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif
    <nav aria-label="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">جميع المنتجات</a></li>
                <li class="breadcrumb-item active" aria-current="page">تعديل منتج</li>
            </ol>
        </nav>
    </nav>
    <hr>
    <div class="row"> 
        <div class="col-md-8">
            <form id="editProductForm" action="{{ route('products.update', $product->id) }}" method="post">
                @csrf
                @method('put')
                {{-- //row --}}
                <div class="row">
                    {{-- col-6 --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ident">المعرّف </label>
                            <input type="text" id="ident" name="ident" value="{{ $product->ident }}"
                                class="form-control {{ $errors->has('ident') ? ' is-invalid' : '' }}"
                                placeholder="قم بقراءة المعرف من اللصاقة" autofocus required autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="shortIdent">رمز المنتج </label>

                            <input id="shortIdent" type="number"
                                class="form-control {{ $errors->has('short_ident') ? ' is-invalid' : '' }}"
                                value="{{ old('short_ident') ?? $product->short_ident }}" name="short_ident">
                            <b
                                class="text-danger">{{ $errors->has('short_ident') ? $errors->first('short_ident') : '' }}</b>

                        </div>
                    </div>
                </div>
                {{-- //row --}}
                <div class="row">
                    {{-- col-6 --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="weight"> الوزن بالغرام</label>
                            <input type="number" step="0.01" id="weight" name="weight"
                                value="{{ $product->weight }}"
                                class="form-control {{ $errors->has('weight') ? ' is-invalid' : '' }}" placeholder="2"
                                autocomplete="off">
                        </div>
                    </div>
                    {{-- col-6 --}}
                    @if ($product->selled_price)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="selledPrice">سعر المبيع</label>
                                <input type="number" id="selledPrice" name="selled_price"
                                    value="{{ $product->selled_price }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    @endif
                </div>
                {{-- //row --}}
                <div class="row">
                    {{-- col-6 --}}
                    @if ($product->selled_date)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">تاريخ المبيع </label>
                                <input type="date" id="price" name="selled_date"
                                    value="{{ \Carbon\Carbon::parse($product->selled_date)->format('Y-m-d') }}"
                                    class="form-control" placeholder="350" autocomplete="off">
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="measur">سعر التحويل</label>
                            <input type="number" step="0.01"  id="ounce_price" name="ounce_price"
                                value="{{ $product->ounce_price }}" class="form-control">
                        </div>
                    </div>
                </div>
                {{-- //row --}}
                <div class="row">
                    {{-- col-6 --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="caliber">{{ __('caliber.caliber') }}</label>
                            <select name="caliber" id="caliber"
                                class="custom-select {{ $errors->has('caliber') ? ' is-invalid' : '' }}"
                                value="{{ old('caliber') }}">
                                <option value="">إختر من هنا</option>
                                @foreach ($calibers as $caliber)
                                    <option value="{{ $caliber->id }}" @if ($product->caliber_id == $caliber->id) selected @endif>
                                        {{ $caliber->full_name }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                    {{-- col-6 --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">{{ __('types.type') }}</label>
                            <select name="type" id="type"
                                class="custom-select {{ $errors->has('type') ? ' is-invalid' : '' }}"
                                value="{{ old('type') }}">
                                <option value="">إختر من هنا</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @if ($product->type_id == $type->id) selected @endif>
                                        {{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                {{-- //row --}}
                <div class="row">
                    {{-- col-6 --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">الحالة</label>
                            <select name="status" id="status" class="custom-select">
                                <option value="1" @if ($product->status == 1)  @endif readonly>مباع</option>
                                <option value="2" @if ($product->status == 2) selected @endif>متوفر</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="desc">الوصف</label>
                            <textarea name="desc" id="desc" rows="5" class="form-control"> {{ $product->description }}</textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-lg">{{ __('buttons.save') }}</button>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        // prevent submitting form when Enter Clicked
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('editProductForm').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
