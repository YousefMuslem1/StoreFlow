@extends('layouts.app')

@section('content')
    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}

        </div>
    @endif
    <nav aria-label="breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">جميع المنتجات</a></li>
                <li class="breadcrumb-item active" aria-current="page">إضافة منتج </li>
            </ol>
        </nav>
    </nav>
    <hr>
    <div class="row">
        <div class="col-sm-3">
            <span>الوزن الكلي: {{ $totalWeightProducts }} g</span>

        </div>
        <div class="col-sm-3">
            <span>آخر وزن مدخل : {{ $lastEnteredWeight }} g</span>

        </div>

        <div class="col-sm-4">
            <form action="" class="form-inline">
                <div class="form-group">
                    <label for="">سعر التحويل:</label>
                    <input type="number" step="0.0000001" id="set_price_ounce" value="" class="form-control">
                </div>
                <button type="button" id="savePriceOunce" class="btn btn-primary mx-1"><i class="fas fa-save"></i></button>
            </form>
        </div>
    </div>
    <hr>

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('products.store') }}" method="post" id="createProductForm">
                @csrf
                <div class="row mb-2">
                    <div class="col-sm-6 col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="quantity_check_box"
                                {{ old('quantity_check_box') ? 'checked' : '' }} id="quantity_selected">
                            <label class="form-check-label" for="quantity_selected">
                                منتج من كمية
                            </label>
                        </div>
                    </div>
                    <div class="col sm-6 col-md-5">
                        <select name="quantity" id="quantity_select"
                            class="type-select  custom-select {{ $errors->has('quantity') ? ' is-invalid' : '' }}"
                            value="{{ old('type') }}">
                            <option value="">إختر من هنا</option>
                            @foreach ($quantites as $quantity)
                                <option value="{{ $quantity->id }}">
                                    {{ $quantity->type->name }}</option>
                            @endforeach
                        </select>
                        <b class="text-danger">{{ $errors->has('quantity') ? $errors->first('quantity') : '' }}</b>
                    </div>
                </div>
                <hr>


                {{-- //row --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="weight"> الوزن بالغرام<span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="weight" name="weight" value="{{ old('weight') }}"
                                class="form-control {{ $errors->has('weight') ? ' is-invalid' : '' }}" placeholder="2"
                                autocomplete="off" autofocus>
                        </div>
                    </div>
                    {{-- col-6 --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ident">المعرّف<span class="text-danger">*</span></label>
                            <input type="text" id="ident" name="ident" value="{{ old('ident') }}"
                                class="form-control {{ $errors->has('ident') ? ' is-invalid' : '' }}"
                                placeholder="قم بقراءة المعرف من اللصاقة" required autocomplete="off">
                            <p class="text-danger">{{ $errors->has('ident') ? $errors->first('ident') : '' }}</p>
                            {{-- <b class="" id="checkMessage"></b> --}}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="">رمز المنتج</label>
                        {{-- <h4 id="shortIdent">{{ $short_ident }}</h4> --}}
                        <input id="shortIdent" type="number"
                            class="form-control {{ $errors->has('short_ident') ? ' is-invalid' : '' }}"
                            value="{{ old('short_ident') ?? $short_ident }}" name="short_ident">
                        <b class="text-danger">{{ $errors->has('short_ident') ? $errors->first('short_ident') : '' }}</b>
                    </div>
                </div>
                {{-- //row --}}
                <div class="row">
                    {{-- col-6 --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="caliber">{{ __('caliber.caliber') }}<span class="text-danger">*</span></label>
                            <select name="caliber" id="caliber"
                                class="custom-select {{ $errors->has('caliber') ? ' is-invalid' : '' }}"
                                value="{{ old('caliber') }}">
                                <option value="">إختر من هنا</option>
                                @foreach ($calibers as $caliber)
                                    <option value="{{ $caliber->id }}" @if (old('caliber') == $caliber->id) selected @endif>
                                        {{ $caliber->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- col-6 --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">{{ __('types.type') }}<span class="text-danger">*</span></label>
                            <select name="type" id="type" required
                                class=" custom-select {{ $errors->has('type') ? ' is-invalid' : '' }}"
                                value="{{ old('type') }}">
                                <option value="">إختر من هنا</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @if (old('type') == $type->id) selected @endif>
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
                            <label for="measur">سعر التحويل</label>
                            <input type="number" step="0.00000001" id="ounce_price" name="ounce_price" value=""
                                class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">الاسم</label>
                            <input type="text" step="0.01" id="name" value="{{ old('name') }}"
                                name="name" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                placeholder="خاتم وردة" autocomplete="off">
                        </div>
                    </div>
                </div>
                {{-- //row --}}

                {{-- //row --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="desc">الوصف</label>
                            <textarea name="desc" id="desc" rows="5" class="form-control"> {{ old('desc') }}</textarea>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg">{{ __('buttons.save') }}</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/3.0.1/js.cookie.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.type-select').select2();
            if (Cookies.get('caliber')) {
                $('#caliber').val(Cookies.get('caliber'));
            }
            if (Cookies.get('type')) {
                $('#type').val(Cookies.get('type'));
            }

            // Set cookies on form submission
            $('#createProductForm').on('submit', function() {
                let selectedCaliber = $('#caliber').val();
                let selectedType = $('#type').val();

                if (selectedCaliber) {
                    Cookies.set('caliber', selectedCaliber, {
                        expires: 7
                    }); // Expires in 7 days
                }
                if (selectedType) {
                    Cookies.set('type', selectedType, {
                        expires: 7
                    }); // Expires in 7 days
                }
            });

            // Load price_ounce from cookies if available
            var savedPriceOunce = Cookies.get('price_ounce');
            if (savedPriceOunce) {
                $('#price_ounce').val(savedPriceOunce);
                $('#set_price_ounce').val(savedPriceOunce);
                $('#ounce_price').val(savedPriceOunce);
            }

            // Save price_ounce to cookies when user clicks save button
            $('#savePriceOunce').click(function() {
                var priceOunce = $('#set_price_ounce').val();
                if (priceOunce) {
                    Cookies.set('price_ounce', priceOunce);
                    alert('تم تحديث سعر الاونصة بنجاح');
                }
            });

            // Optional: Update the form field when the cookie is set
            $('#set_price_ounce').on('input', function() {
                $('#ounce_price').val($(this).val());
            });
        });
        // prevent submitting form when Enter Clicked
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('createProductForm').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
