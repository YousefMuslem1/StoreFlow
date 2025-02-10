@extends('employer.layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">الكميّات</h1>
    </div>
    <hr>
    @if (session('success_message'))
        <div class="alert alert-success">
            {{ session('success_message') }}
        </div>
    @endif
    @if (session('error_message'))
        <div class="alert alert-danger">
            {{ session('error_message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <form action="{{ route('sell.quantity.store') }}" method="POST" id="sell" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="proccess_type">نوع العملية</label>
                <select name="proccess" id="proccess_type" class="form-control" required autofocus>
                    <option value="">----------- اختر نوع العملية --------------</option>
                    <option value="1" {{ old('proccess' == 1 ? 'selected' : '') }}>بيع وزن من كميّة</option>
                    <option value="9" {{ old('proccess' == 9 ? 'selected' : '') }}>شراء خشر</option>
                </select>
            </div>

            {{-- <div class="form-group mt-2 d-none" id="price_section">
                <label for="product_short_ident">رمز المنتج</label>
                <input type="number" id="product_short_ident" name="product_short_ident" class="form-control">
            </div> --}}

            <div id="productInfoSection" class="border border-success p-2 d-none">
                <div class="row">
                    <div class="col-sm-2">
                        <span>الرمز:</span><span id="productShortId"></span>
                    </div>
                    <div class="col-sm-3">
                        <span> الوزن:</span><span id="productWeight"></span>
                    </div>
                    <div class="col-sm-4">
                        <span> الصنف:</span><span id="productType"></span>
                    </div>
                    <div class="col-sm-3">
                        <span> العيار:</span><span id="productCaliber"></span>
                    </div>
                </div>
            </div>
            <b class="text-danger d-none" id="productNotFound">لم يتم العثور على المنتج يرجى اعادة ادخال الرمز
                مجدداً</b>

            <div class="form-group mt-2">
                <label for="type">الصنف</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">------------ اختر نوع الصنف --------------</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" {{ old('type') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mt-2" required>
                <label for="weight">الوزن(المراد سحبه/إضافته من/الى الكميّة)</label>
                <input type="number" step="0.01" class="form-control" name="weight" required>
            </div>

            <div id="PaymentOptions" class="d-none">
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
            </div>
            <hr>

            <div class="form-group mt-2">
                <label for="price">سعر المبيع/الشراء الإجمالي</label>
                <input type="number" name="price" class="form-control" step="0.01" required>
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

                    <img src="{{ asset('img/loading.gif') }}" class="loading-gif d-none" width="20" alt="">
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

            <div class="form-group mt-2">
                <label for="notice">إضافة ملاحظة</label>
                <textarea name="notice" id="notice" cols="30" rows="3" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success mt-2"> حفظ</button>
        </form>
    </div>
@endsection
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $('#proccess_type').on('change', function() {
            console.log('yes')
            if ($(this).val() == '1') {
                $('#PaymentOptions').removeClass('d-none');
            } else {
                $('#PaymentOptions').addClass('d-none');
            }
        });


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

                    if ($('input[name="newCusotmerRadio"]:checked').val() ==
                        1) { // اسم العميل ورقم الهاتف مطلوبين
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
                        if (!customerIsFounded) {
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
                            } else if (selectedValue == '1' && Object.keys(response)
                                .length === 0) {
                                NewCustomerStatus = true;
                                // $('.get_customer_details').addClass(
                                //     'd-none')
                                // $('.customerNotFound').removeClass(
                                //     'd-none')
                            } else if (selectedValue == '2' && Object.keys(response)
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
                            } else if (selectedValue == '2' && Object.keys(response)
                                .length === 0) {
                                customerIsFounded = false;
                                $('.customerAjaxAlert').removeClass('d-none').text(
                                    'الاسم غير موجود')
                            } else {


                            }

                            // Handle the response from the server
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            $('.loading-gif').addClass('d-none')
                            // Handle any errors that occur during the request
                            // $('#customerNameInfo').text(response
                            //         .name)
                            //     $('#customerPhoneInfo').text(response
                            //         .phone)
                            //     $('.get_customer_details').removeClass(
                            //         'd-none')
                            //     $('.customerNotFound').addClass(
                            //         'd-none')
                        }
                    });
                }, 1500);
            });
            // } else {
            //     // Remove the input event listener if other radio is selected
            //     $('#customerName').off('input');
            // }


        });
    </script>
@endsection
