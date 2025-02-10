@extends('employer.layouts.app')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">البحث عن منتج</h1>
    </div>

    <form action="{{ route('selling_search') }}" method="get">
        @csrf
        <input type="text" name="ident" id="ident" class="form-control" autofocus autocomplete="off">
        <button type="submit" id="searchButton" class="btn btn-success mt-3">بحث</button>
        <a href="{{ route('sell.index') }}" class="btn btn-secondary mt-3">تحديث</a>

    </form>

    <div class="text-center">
    </div>
    <!-- Display the scanned QR code value -->
    {{-- <div id="qr-value"></div> --}}
    @if (isset($product))
        <div id="searchResult">
            <div class="table-responsive">
                <table class="table table-bordered  table-striped mx-auto mt-4">
                    <tbody>
                        <tr>
                            <th scope="row">رقم المعرّف</th>
                            <td id="identity">{{ $product->ident }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-warning"> رمز المنتج</th>
                            <td id="identity" class="text-warning"><b>{{ $product->short_ident }}</b></td>
                        </tr>

                        <tr>
                            <th scope="row" class="text-danger">الوزن</th>
                            <td class="text-danger"><b>{{ $product->weight }}</b> g</td>
                        </tr>

                        <tr>
                            <th scope="row"> الحالة</th>
                            <td id="status">{{ $product->status == 2 ? 'متوفر' : 'مباع' }}</td>
                            <td id="statusDetect" style="display: none">{{ $product->status }}</td>
                        </tr>
                        <tr>
                            <th scope="row">السعر</th>
                            <td>{{ $product->calculateSellingPrice() }} €</td>
                        </tr>
                        <tr>
                            <th scope="row">العيار</th>
                            <td>{{ $product->caliber->full_name }}</td>
                        </tr>
                        <tr>
                            <th scope="row">النوع</th>
                            <td>{{ $product->type->name }}</td>
                        </tr>
                        <tr>
                            <th scope="row">سعر الغرام</th>
                            <td>{{ $product->caliber->caliber_price }}</td>
                        </tr>
                        <tr>
                            <th scope="row">ملاحظة</th>
                            <td class="text-success">{{ $product->description ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- {Product not selled yet} -->
        @if ($product->status == 2)
            <form action="{{ route('sell.store') }}" method="POST" id="sell" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="ident" value="{{ $product->ident }}">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="paymentdWay" id="fullPrice" value="1" checked>
                    <label class="form-check-label" for="fullPrice">
                        دفع كامل
                    </label>
                </div>
                <div class="form-check form-check-inline mb-1">
                    <input class="form-check-input" type="radio" name="paymentdWay" id="installmentPrice" value="2">
                    <label class="form-check-label" for="installmentPrice">
                        تقسيط
                    </label>
                </div>
                <hr>
                <div class="form-group">
                    <label for="price">سعر المبيع الإجمالي <b class="text-danger">*</b></label>
                    <input type="number" class="form-control" id="price" name="price">
                </div>

                <div class="customer_details d-none">
                    <div class="form-group">
                        <label for="payedMoney"> المبلغ المدفوع<b class="text-danger">*</b></label>
                        <input type="number" class="form-control" id="payedMoney" name="payedMoney">
                    </div>
                    <h6>-------معلومات العميل---------</h6>
                    <hr>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="newCusotmerRadio" id="newCusotmerRadio"
                            value="1" checked>
                        <label class="form-check-label" for="newCusotmerRadio">
                            جديد
                        </label>
                    </div>
                    <div class="form-check form-check-inline">

                        <input class="form-check-input" type="radio" name="newCusotmerRadio" id="oldCusotmerRadio"
                            value="2">
                        <label class="form-check-label" for="oldCusotmerRadio">
                            موجود مسبقاً
                        </label>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="customerName" class="d-block customernamelabel"> اسم العميل<b
                                class="text-danger">*</b></label>
                        <input type="text" class="form-control" id="customerName" name="customerName"
                            value="{{ old('customerName') }}">

                        <img src="{{ asset('img/loading.gif') }}" class="loading-gif d-none" width="20"
                            alt="">
                    </div>

                    <table class="table table-bordered get_customer_details d-none">
                        <tbody>
                            <tr>
                                <td id="customerNameInfo">username</td>
                                <td>اسم العميل</td>
                            </tr>
                            <tr>
                                <td id="customerPhoneInfo">099448756</td>
                                <td>رقم الهاتف</td>
                            </tr>
                        </tbody>
                    </table>
                    <b class="text-danger d-none customerAjaxAlert">لم يتم العثور على العميل</b>
                    <div class="form-group customerPhone">
                        <label for="customerPhone">رقم الهاتف <b class="text-danger">*</b></label>
                        <input type="number" class="form-control" id="customerPhone" name="customerPhone"
                            value="{{ old('customerPhone') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notice">ملاحظة</label>
                    <textarea name="notice" class="form-control" cols="30" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-success mt-2">اتمام البيع</button>
            </form>
        @endif
    @endif
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var debounceTimer;
            var customerIsFounded = false; // العميل قديم 
            var NewCustomerStatus = false; // العميل جديد
            var selectedValue = $('input[name="newCusotmerRadio"]').val();

            //طريقة الدفع
            $('input[name="paymentdWay"]').on('change', function() {
                var selectedValue = $('input[name="paymentdWay"]:checked').val();
                if (selectedValue == 2) { // تقسيط
                    $('.customer_details').removeClass('d-none')
                } else {
                    $('.customer_details').addClass('d-none')
                }
            });
            //العميل جديد ام قديم
            $('input[name="newCusotmerRadio"]').on('change', function() {
                 selectedValue = $('input[name="newCusotmerRadio"]:checked').val();
                 $('#customerName').val('')
                 console.log(selectedValue);
                if (selectedValue == 2) { // موجود مسبقا
                    $('.customerPhone').addClass('d-none')
                    $('.customernamelabel').text('اسم العميل / رقم هاتف')

                } else {
                    $('.customerPhone').removeClass('d-none')
                    $('.customernamelabel').text('اسم العميل ')
                }
            });
            var form = document.getElementById("sell");

            // الضغط على زر حفظ بعد احال جميع الحقول
            form.addEventListener("submit", function(event) {
                event.preventDefault(); // Prevent the form from submitting immediately
                var price = $('#price').val();
                if (price < 6) {
                    alert('السعر غير صحيح!')
                    return;
                }
                if ($('input[name="paymentdWay"]:checked').val() == 2) { // تحديد الدفع بالتقسيط
                    var payedMoney = $('#payedMoney').val();
                    if (payedMoney < 6) {
                        alert('يرجى تحديد مبلغ الدفعة');
                        return
                    }

                    if ($('input[name="newCusotmerRadio"]:checked').val() == 1) { // اسم العميل ورقم الهاتف مطلوبين
                        if ($('#customerName').val() === '') {
                            alert('يرجى ادخال اسم العميل')
                            return
                        }
                        if ($('#customerPhone').val() === '') {
                            alert('يرجى ادخال رقم هاتف العميل')
                            return
                        }
                        if (!NewCustomerStatus) {
                            console.log(NewCustomerStatus)
                            alert('يرجى إدخال اسم العميل')
                            return
                        }
                    } else {
                        if (!customerIsFounded ) {
                            alert('يرجى إدخال اسم العميل')
                            return
                        }
                        
                    }

                } // تحديد الدفع بالتقسيط

                // Display a confirmation dialog
                var confirmed = confirm("هل تريد اتمام عملية البيع ؟");
                // If user confirms, submit the form
                if (confirmed) {
                    form.submit();
                }
            }); // submit end

            //عميل موحود مسبقا, ارسال طلب لجلب معلومات العميل
            // if (customerSelectType == '2') {
            // Add input event listener to customerName only if radio value is 2
            $('#customerName').on('input', function() {
                $('.loading-gif').removeClass('d-none');
                $('.customerAjaxAlert').addClass('d-none')
                $('.get_customer_details').addClass('d-none')
                clearTimeout(debounceTimer);
                var customer = $(this).val();
                console.log(selectedValue);
                debounceTimer = setTimeout(function() {
                    $.ajax({
                        method: 'GET',
                        url: '/customer/' +
                            customer, // Replace with your endpoint
                        data: {
                            customer: customer,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            // console.log('Response:', response);
                            $('.loading-gif').addClass('d-none')
                            // عميل جديد والاسم موجود بالفعل
                            if (selectedValue == '1' && Object.keys(response)
                                .length !== 0) {
                                NewCustomerStatus = false;
                                $('.customerAjaxAlert').removeClass('d-none').text(
                                    'هذا الاسم مستخدم من قبل!')
                                    console.log(NewCustomerStatus)
                            }
                            else if(selectedValue == '1' && Object.keys(response)
                                .length === 0) {
                                    NewCustomerStatus = true;
                            }
                            else if(selectedValue == '2' && Object.keys(response)
                                .length !== 0) {
                                 customerIsFounded = true;
                                $('#customerNameInfo').text(response
                                    .name)
                                $('#customerPhoneInfo').text(response
                                    .phone)
                                $('.get_customer_details').removeClass(
                                    'd-none')
                                $('.customerNotFound').addClass(
                                    'd-none')
                            }else if(selectedValue == '2' && Object.keys(response)
                                .length === 0){
                                     customerIsFounded = false;
                                    $('.customerAjaxAlert').removeClass('d-none').text('الاسم غير موجود')
                                }else {
                            

                                }

                            // Handle the response from the server
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            $('.loading-gif').addClass('d-none')

                        }
                    });
                }, 1500);
            });

        });
    </script>
@endsection
