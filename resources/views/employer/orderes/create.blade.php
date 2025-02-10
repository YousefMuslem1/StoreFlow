@extends('employer.layouts.app')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"> إضافة عربون جديد </h1>
    </div>
    <hr>
    @if (session('order_added'))
        <div class="alert alert-success">
            تم إضافة الطلب بنجاح
        </div>
        @php
            $order = session('order_added');
        @endphp
        <div class="row">
            <div class="col-md-5">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>إسم العميل</th>
                            <td>{{ $order->customer->name }}</td>
                        </tr>

                        <tr>
                            <th>المبلغ المستلم</th>
                            <td>{{ $order->amount_paid }} <b>€</b></td>
                        </tr>
                        <tr>
                            <th>المسؤول</th>
                            <td>{{ $order->user->name }}</td>
                        </tr>
                        <tr>
                            <th>الحالة</th>
                            <td>{{ $order->status_name }}</td>
                        </tr>
                        <tr>
                            <th>وصف الطلب</th>
                            <td>{{ $order->notice }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
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

    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('orderes.store') }}" method="POST" id="order-form">
                @csrf
                <div class="form-group">
                    <label for="customerName">إسم العميل</label>
                    <input type="text" class="form-control" id="customerName" name="customer_name" autofocus
                        autocomplete="off">
                    <input type="hidden" id="customerId" name="customer_id">
                </div>

                <div class="form-group d-none" id="customerPhoneContianer">
                    <label for="customerPhone">رقم هاتف العميل</label>
                    <input type="number" name="customer_phone" class="form-control" id="customerPhone">
                </div>
                <img src="{{ asset('img/loading.gif') }}" class="loading-gif d-none" width="20" alt="">



                <table class="table table-bordered get_customer_details d-none">
                    <tbody>
                        <tr>
                            <th>إسم العميل</th>
                            <td id="customerNameInfo">yousef</td>
                        </tr>

                        <tr>
                            <th>رقم الهاتف</th>
                            <th id="customerPhoneInfo">0994419082</th>
                        </tr>
                    </tbody>
                </table>

                <b class="text-danger d-none customerAjaxAlert"> هذا العميل غير موجود سيتم إنشاء إسم جديد </b>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="productIsSet" id="showInputCheckbox">
                    <label class="form-check-label" for="showInputCheckbox">المنتج متوفر </label>
                </div>

                <div class="form-group mt-3" id="productNumberInput" style="display: none;">
                    <label for="productNumber">رمز المنتج</label>
                    <input type="number" class="form-control" name="product_number" id="productNumber" placeholder="Enter product number">
                </div>
                <table class="table table-bordered" id="productDetailsTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>رمز المنتج</th>
                            <th>الوزن</th>
                            <th>النوع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td id="productName"></td>
                            <td id="productWeight"></td>
                            <td id="productTypeName"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="form-group mt-3" id="productPriceInput" style="display: none;">
                    <label for="productPrice"> سعر المبيع</label>
                    <input type="number" step="0.01" class="form-control" name="proudct_selled_price" id="productPrice" placeholder="Enter product Price">
                </div>

     

                <div class="form-group">
                    <label for="amountPaid">المبلغ المدفوع</label>
                    <input type="number" class="form-control" step="0.1" id="amountPaid" name="amount_paid">
                </div>
                <div class="form-group">
                    <label for="notice">وصف الطلب</label>
                    <textarea name="notice" class="form-control" id="notice" cols="30" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-success">حفظ البيانات</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        var customerisNew = true;
        var customerPhone;
        var productIsSet = false;
        var selled_price ;
        var form = document.getElementById("order-form");
        form.addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent the form from submitting immediately
            customerPhone = $('#customerPhone').val();

            if (customerisNew && !customerPhone) {
                alert('يرجى إدخال رقم هاتف العميل');
                return;
            }

            if ($('#showInputCheckbox').is(':checked')) {
                if(!productIsSet){
                    alert('يرجى ادخال رمز المنتج')
                    return
                }
                selled_price = $('#productPrice').val()
                console.log(selled_price)

                if(selled_price < 5){
                    console.log(selled_price)
                    alert('يرجى ادخال سعر المبيع')
                    return
                }
            } 


            // Display a confirmation dialog
            var confirmed = confirm("هل تريد اتمام العملية  ؟");
            // If user confirms, submit the form
            if (confirmed) {
                form.submit();
            }
        });
        var debounceTimer;
        $('#customerName').on('input', function() {
            $('.loading-gif').removeClass('d-none');
            $('.customerAjaxAlert').addClass('d-none')
            $('.get_customer_details').addClass('d-none')
            clearTimeout(debounceTimer);
            var customer = $(this).val();
            debounceTimer = setTimeout(function() {
                $.ajax({
                    method: 'GET',
                    url: '/customer/' +
                        customer, // Replace with your endpoint

                    data: {
                        customer: customer,
                        customerPhone,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // console.log('Response:', response);
                        $('.loading-gif').addClass('d-none')
                        // عميل جديد والاسم موجود بالفعل
                        if (Object.keys(response)
                            .length !== 0) {
                            customerisNew = false;
                            $('#customerPhoneContianer').addClass('d-none');

                            $('#customerNameInfo').text(response
                                .name)
                            $('#customerPhoneInfo').text(response
                                .phone)
                            $('#customerId').val(response.id)
                            $('.get_customer_details').removeClass(
                                'd-none')
                            $('.customerNotFound').addClass(
                                'd-none')
                        } else if (Object.keys(response)
                            .length === 0) {
                            customerisNew = true;
                            $('#customerPhoneContianer').removeClass('d-none');
                            $('.customerAjaxAlert').removeClass(
                                'd-none')
                            $('#customerId').val('')
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


        // Show or hide the input field based on checkbox
        $('#showInputCheckbox').change(function() {
            if ($(this).is(':checked')) {
                $('#productNumberInput').show();
                $('#productPriceInput').show();
            } else {
                productIsSet = false;
                $('#productNumberInput').hide();
                $('#productPriceInput').hide();
                $('#productDetailsTable').hide(); // Hide the table when input is hidden
            }
        });

        let typingTimer; // Timer identifier
        const doneTypingInterval = 500; // Time in ms (0.5 seconds)
        const $input = $('#productNumber');

        // On keyup, start the countdown
        $input.on('keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        });

        // On keydown, clear the countdown 
        $input.on('keydown', function() {
            clearTimeout(typingTimer);
        });

        // User is "finished typing," do something
        function doneTyping() {
            let productNumber = $input.val();
            console.log(productNumber);
            if (productNumber) {
                // Make AJAX request to get product details
                $.ajax({
                    url: '/sell/show/' , // Replace with your API endpoint
                    type: 'GET',
                    data: {
                        identifier: productNumber
                    },
                    success: function(response) {
                        // Assuming the response is in JSON format with keys: short_ident, price, description
                        productIsSet = true;
                        $('#productName').text(response.short_ident);
                        $('#productWeight').text(response.weight + ' g');
                        $('#productTypeName').text(response.type.name);
                        $('#productDetailsTable').show();
                    },
                    error: function() {
                        $('#productDetailsTable').hide();
                        alert('المنتج غير متوفر');
                    }
                });
            } else {
                productIsSet = false;
                $('#productDetailsTable').hide();
            }
        }
    </script>
@endsection
