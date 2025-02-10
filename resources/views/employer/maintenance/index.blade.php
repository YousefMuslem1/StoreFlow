@extends('employer.layouts.app')
@section('styles')
    <style>
        .lazyload {
            opacity: 0;
            transition: opacity 0.3s;
        }

        .lazyloaded {
            opacity: 1;
        }

        .modal-body {
            max-height: 60vh;
            /* Adjust as needed */
            overflow-y: auto;
        }
    </style>
@endsection
@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">إدارة طلبات الصيانة</h1>
    </div>
    <hr>
    <!-- Button to open the modal -->
    <button type="button" class="btn btn-success mb-2" data-toggle="modal" data-target="#addOrderModal">
        طلب جديد <i class="fas fa-plus"></i>
    </button>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الوزن</th>
                    <th>التكلفة الأولية</th>
                    <th>التكلفة النهائية</th>
                    <th>الحالة</th>
                    <th>تاريخ الاستلام</th>
                    <th>المستلم</th>
                    <th>صورة</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($orders as $order)
                    @php
                        $productImages = json_decode($order->product_images, true);
                        $statusColor = ['', 'warning', 'success', 'danger', 'primary', 'danger', 'success'];
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->customer->name }}</td>
                        <td>{{ $order->weight }} <b>g</b></td>
                        <td>{{ $order->cost }} <b>€</b></td>
                        <td>{{ $order->last_cost > 0 ?? '-' }} <b>€</b></td>
                        <td class="text-{{ $statusColor[$order->status] }}">{{ $order->status_name }}</td>
                        <td>{{ $order->recevieved_date }}</td>
                        <td>{{ $order->user->name }}</td>
                        @if (!empty($productImages) && isset($productImages[0]))
                            <td><img class="lazyload" data-src="{{ asset('storage/' . $productImages[0]) }}" width="50"
                                    height="50" alt="Product Image" style="max-width: 100px; height: auto;"
                                    loading="lazy"></td>
                        @else
                            <td></td>
                        @endif
                        <td class="d-flex">
                            <button class="btn btn-primary showOrderBtn btn-sm" data-order="{{ json_encode($order) }}"><i
                                    class="fas fa-eye"></i></button>
                            @if ($order->status !== 3 && $order->status !== 5 && $order->status !== 2 && $order->status !== 6)
                                <button class="btn btn-danger cancelOrder btn-sm m-1" data-toggle="modal"
                                    data-target="#cancelModal" data-order="{{ json_encode($order) }}"><i
                                        class="fas fa-times-circle"></i></button>
                                <button class="btn btn-success btn-sm reciveOrder" data-toggle="modal"
                                    data-target="#reciveModal" data-order="{{ json_encode($order) }}"><i
                                        class="fas fa-shipping-fast"></i></button>
                            @endif

                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
    <div id="paginate">
        {{ $orders->appends(request()->query())->links() }}
    </div>

    <!-- recive modal -->
    <div class="modal fade" id="reciveModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel"> تسليم طلب صيانة</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>العميل</th>
                                    <td id="recivecustomerName"></td>
                                </tr>
                                <tr>
                                    <th> رقم الهاتف</th>
                                    <td id="recivecustomerPhone"></td>
                                </tr>
                                <tr>
                                    <th>وزن المنتج</th>
                                    <td id="recivecustomerWeight"></td>
                                </tr>
                                <tr>
                                    <th>التكلفة الأولية</th>
                                    <td id="recivecustomerCost"></td>
                                </tr>
                                <tr>
                                    <th>الحالة</th>
                                    <td id="recivecustomerStatus"></td>
                                </tr>
                                <tr>
                                    <th>سجل الملاحظات</th>
                                    <td id="recivecustomerNotice"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-success d-none" id="reciveSuccessAlert">تم تسليم طلب الصيانة بنجاح</div>
                        <div class="form-group">
                            <label for="lastCost">التكلفة النهائية</label>
                            <input type="number" step="0.01" class="form-control" id="lastCost">
                            <input type="hidden" id="firstCost" value="">
                        </div>
                        <div class="form-group">
                            <input type="hidden" id="orderReciveId" value="">
                            <label for="reciveNotice"> ملاحظات التسليم</label>
                            <textarea id="reciveNotice" cols="30" rows="4" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="closeRecivelBtn">إغلاق</button>
                        <button type="button" class="btn btn-danger" id="saveReciveOrderBtn">حفظ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Cancel modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel"> إلغاء طلب صيانة</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>العميل</th>
                                    <td id="CancelcustomerName"></td>
                                </tr>
                                <tr>
                                    <th> رقم الهاتف</th>
                                    <td id="CancelcustomerPhone"></td>
                                </tr>
                                <tr>
                                    <th>وزن المنتج</th>
                                    <td id="CancelcustomerWeight"></td>
                                </tr>
                                <tr>
                                    <th>التكلفة</th>
                                    <td id="CancelcustomerCost"></td>
                                </tr>
                                <tr>
                                    <th>الحالة</th>
                                    <td id="CancelcustomerStatus"></td>
                                </tr>
                                <tr>
                                    <th>سجل الملاحظات</th>
                                    <td id="CancelcustomerNotice"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-success d-none" id="cancelSuccessAlert">تم الغاء طلب الصيانة بنجاح</div>
                        <div class="form-group">
                            <input type="hidden" id="orderCancelId" value="">
                            <label for="cancelReason">سبب الإلغاء</label>
                            <textarea id="cancelReason" cols="30" rows="4" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="closeCancelBtn">إغلاق</button>
                        <button type="button" class="btn btn-danger" id="saveCancelOrderBtn">حفظ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Details Modal  -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">تفاصيل الطلب </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th>العميل</th>
                                            <td id="showCustomerName"></td>
                                        </tr>
                                        <tr>
                                            <th>رقم الهاتف</th>
                                            <td id="showCustomerPhone"></td>
                                        </tr>
                                        <tr>
                                            <th> حالة الطلب</th>
                                            <td id="showOrderStatus"></td>
                                        </tr>
                                        <tr>
                                            <th>الوزن المستلم</th>
                                            <td id="showCustomerWeight"></td>
                                        </tr>
                                        <tr>
                                            <th> المسؤول</th>
                                            <td id="showUser"></td>
                                        </tr>
                                        <tr>
                                            <th> التكلفة الأوّليّة</th>
                                            <td id="showCost"></td>
                                        </tr>
                                        <tr>
                                            <th> التكلفة النهائية</th>
                                            <td id="showLastCost"></td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الإستلام</th>
                                            <td id="showReceivedDate"></td>
                                        </tr>
                                        <tr>
                                            <th> سجل الملاحظات</th>
                                            <td id="showNotice"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <h6>صورة</h6>
                                <div id="modalProductImages"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add new order -->
    <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">

            <form id="addOrderForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addOrderModalLabel">إضافة طلب صيانة جديد</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="responseSuccessContent" class="d-none">
                            <div class="alert alert-success">تم إضافة الطلب بنجاح</div>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr class="table-primary">
                                        <th>اسم العميل</th>
                                        <td id="responseCustomerName"></td>
                                    </tr>
                                    <tr class="table-warning">
                                        <th>المبلغ المدفوع</th>
                                        <td id="responseCost"></td>
                                    </tr>
                                    <tr class="table-info">
                                        <th>الوزن المستلم</th>
                                        <td id="responseWeight"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @csrf
                        <div class="form-group">
                            <label for="customer_id">العميل</label>
                            <input type="string" class="form-control" id="customer_name" name="customer_name"
                                autocomplete="off" required>
                            <input type="hidden" id="customerId">
                        </div>

                        <div class="form-group d-none" id="customerPhoneContianer">
                            <label for="customerPhone">رقم هاتف العميل</label>
                            <input type="number" name="customer_phone" class="form-control" id="customerPhone">
                        </div>
                        <img src="{{ asset('img/loading.gif') }}" class="loading-gif d-none" width="20"
                            alt="">

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

                        <div class="form-group">
                            <label for="weight">الوزن</label>
                            <input type="number" class="form-control" id="weight" name="weight" required>
                        </div>
                        <div class="form-group">
                            <label for="cost">التكلفة</label>
                            <input type="number" class="form-control" id="cost" name="cost" required>
                        </div>
                        <div class="form-group">
                            <input type="checkbox" id="notPaid" name="notPaid" checked>
                            <label for="notPaid">غير مقبوض</label>
                        </div>
                        <div class="form-group">
                            <label for="notice">ملاحظات</label>
                            <textarea class="form-control" id="notice" name="notice"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="product_image">صورة</label>
                            <input type="file" class="form-control-file" id="product_images" name="product_images[]"
                                accept="image/*" capture="camera" multiple>

                        </div>
                        <!-- Progress bar -->
                        <div class="progress" style="display:none; margin-top: 20px;">
                            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <div class="alert alert-warning d-none" id="requestProcessing"> يرجى الانتظار جاري المعالجة...<img
                                src="{{ asset('img/loading.gif') }}" width="30" alt=""></div>
                        <div id="imagePreviewContainer"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            id="closeBtn">إغلاق</button>
                        <button type="button" class="btn btn-primary" id="saveOrderBtn">حفظ</button>
                    </div>
            </form>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@latest/dist/browser-image-compression.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>

    <script>
        var customer_name;
        var customer_phone;
        var weight;
        var cost;
        var customer_id;
        $(document).ready(function() {
            //display images when they tooken
            $('#product_images').on('change', function() {
                var files = this.files;
                var previewContainer = $('#imagePreviewContainer');
                // previewContainer.empty(); // Clear any existing thumbnails

                if (files) {
                    for (var i = 0; i < files.length; i++) {
                        var file = files[i];
                        if (file.type.startsWith('image/')) {
                            var reader = new FileReader();
                            reader.onload = (function(file) {
                                return function(event) {
                                    var img = $('<img>').attr('src', event.target.result)
                                        .addClass('img-thumbnail').css({
                                            'max-width': '100px',
                                            'margin': '10px'
                                        });
                                    previewContainer.append(img);
                                };
                            })(file); // Create a closure to preserve the value of `file`
                            reader.readAsDataURL(file);
                        }
                    }
                }

            });
        });
        var customerisNew = true;
        var customerPhone;

        $('#saveOrderBtn').on('click', function(e) {
            $(this).prop('disabled', true)
        })
        $('#saveCancelOrderBtn').on('click', function(e) {
            $(this).prop('disabled', true)
        })
        $('#saveReciveOrderBtn').on('click', function(e) {
            $(this).prop('disabled', true)
        })
        $('#closeBtn').click(function() {
            location.reload();
        })
        $('#closeCancelBtn').click(function() {
            location.reload();
        })
        $('#closeRecivelBtn').click(function() {
            location.reload();
        })


        // add new order 
        $('#saveOrderBtn').on("click", async function(event) {
            // Options for compression
            const options = {
                maxSizeMB: 0.5, // Target size in MB
                maxWidthOrHeight: 1920, // Max width or height
                useWebWorker: true
            };
            event.preventDefault(); // Prevent the form from submitting immediately
            customerPhone = $('#customerPhone').val();
            customerName = $('#customer_name').val();
            var notPaid = $('#notPaid').is(':checked');
            weight = $('#weight').val();
            cost = $('#cost').val();
            console.log(notPaid);
            if (customerisNew && !customerPhone) {
                $('#saveOrderBtn').prop('disabled', false)
                alert('يرجى إدخال رقم هاتف العميل');

                return;
            }
            if (weight === '') {
                $('#saveOrderBtn').prop('disabled', false)
                alert('يرجى ادخال الوزن')
                return
            }
            if (cost === '') {
                $('#saveOrderBtn').prop('disabled', false)
                alert('يرجى ادخال التكلفة')
                return
            }
            // Display a confirmation dialog
            var confirmed = confirm("هل تريد اتمام العملية  ؟");
            // If user confirms, submit the form
            if (!confirmed) {
                $('#saveOrderBtn').prop('disabled', false)

                return
            }

            var formData = new FormData();
            formData.append('customer_id', $('#customerId').val());
            formData.append('weight', $('#weight').val());
            formData.append('cost', $('#cost').val());
            formData.append('notice', $('#notice').val());
            formData.append('customerPhone', customerPhone);
            formData.append('customerName', customerName);
            formData.append('customerIsNew', customerisNew);
            formData.append('notPaid', notPaid);
            formData.append('_token', '{{ csrf_token() }}');
            var files = $('#product_images')[0].files;

            $('#requestProcessing').removeClass('d-none')
            for (var i = 0; i < files.length; i++) {
                try {
                    const compressedFile = await imageCompression(files[i], options);
                    formData.append('product_images[]', compressedFile);
                } catch (error) {
                    console.error('Compression error:', error);
                }
            }
            // Show the progress bar
            $('.progress').show();
            $('#requestProcessing').addClass('d-none')
            $.ajax({
                url: '{{ route('maintenance.store') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    // Upload progress
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);

                            // Update the progress bar
                            $('#progress-bar').width(percentComplete + '%');
                            $('#progress-bar').text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#responseSuccessContent').removeClass('d-none')
                    $('#responseCustomerName').text(response.data.customer.name)
                    $('#responseCost').text(response.data.cost + ' €')
                    $('#responseWeight').text(response.data.weight + ' g')
                    // Optionally, refresh the page or update the UI to reflect the new order
                    // Scroll to the table
                    // Show the table
                    // Get the modal body and the target element
                    var modalBody = $('#addOrderModal .modal-body');
                    var targetElement = $('#responseSuccessContent');
                    // Log the positions
                    console.log('modalBody position:', modalBody.position());
                    console.log('targetElement position:', targetElement.position());
                    console.log('modalBody scrollTop:', modalBody.scrollTop());
                    // Calculate the scroll position
                    var scrollPosition = targetElement.position().top + modalBody.scrollTop();


                    console.log('Calculated scroll position:', scrollPosition);

                    // Animate the scroll
                    modalBody.animate({
                        scrollTop: scrollPosition
                    }, 1000);
                },
                error: function(response) {
                    console.log(response);
                },
                complete: function() {
                    // Hide the progress bar
                    $('.progress').hide();
                    $('#progress-bar').width('0%');
                    $('#progress-bar').text('0%');
                }
            });
        });

        // cancel order 
        $('#saveCancelOrderBtn').on("click", function(event) {
            var cancelNotice = $('#cancelReason').val();
            console.log(cancelNotice);
            if (cancelNotice.trim() == '') {
                $('#saveCancelOrderBtn').prop('disabled', false)
                alert('يرجى إدخال سبب الالغاء')
                return
            }
            var confirmed = confirm("هل تريد اتمام العملية  ؟");
            // If user confirms, submit the form
            if (!confirmed) {
                $('#saveCancelOrderBtn').prop('disabled', false)

                return
            }
            var formData = new FormData();
            formData.append('order_id', $('#orderCancelId').val());
            formData.append('cancelNotice', cancelNotice);
            formData.append('_token', '{{ csrf_token() }}');
            $.ajax({
                url: '{{ route('maintenance.cancel') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,

                success: function(response) {
                    $('#cancelSuccessAlert').removeClass('d-none')
                },
                error: function(response) {
                    console.log(response);
                },

            });
        });

        // recive order 
        $('#saveReciveOrderBtn').on("click", function(event) {
            var reciveNotice = $('#reciveNotice').val();
            var firstCost = $('#firstCost').val();
            var lastCost = $('#lastCost').val();
            console.log(firstCost, lastCost);
            // if (reciveNotice.trim() == '') {
            //     $('#saveReciveOrderBtn').prop('disabled', false)
            //     alert('يرجى إدخال سبب الالغاء')
            //     return
            // }
            if (lastCost.trim() == '') {
                $(this).prop('disabled', false)

                alert('يرجى إدخال التكلفة النهائية')
                return
            }
            if (parseFloat(lastCost) < parseFloat(firstCost)) {
                $(this).prop('disabled', false)

                alert('لا يجب أن يكون المبلغ المدخل أقل من ' + firstCost + ' €');
                return
            }
            var confirmed = confirm("هل تريد اتمام العملية  ؟");
            // If user confirms, submit the form
            if (!confirmed) {
                $('#saveReciveOrderBtn').prop('disabled', false)

                return
            }
            var formData = new FormData();
            formData.append('order_id', $('#orderReciveId').val());
            formData.append('last_cost', lastCost);
            formData.append('reciveNotice', reciveNotice);
            formData.append('_token', '{{ csrf_token() }}');
            $.ajax({
                url: '{{ route('maintenance.recive') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,

                success: function(response) {
                    $('#reciveSuccessAlert').removeClass('d-none')
                },
                error: function(response) {
                    console.log(response);
                },

            });
        });


        // display order details modal
        $('.showOrderBtn').click(function() {
            var order = $(this).data('order');
            console.log(order);
            // Parse product_images if it's a JSON string
            var productImages = order.product_images;
            if (typeof productImages === 'string') {
                try {
                    productImages = JSON.parse(productImages);
                } catch (e) {
                    console.error('Error parsing product images:', e);
                    productImages = [];
                }
            }

            // Populate the modal with order details
            $('#showCustomerName').text(order.customer.name);
            $('#showCustomerPhone').text(order.customer.phone);
            $('#showCustomerWeight').text(order.weight + ' g');
            $('#showCost').text(order.cost + ' €');
            $('#showLastCost').text(order.last_cost + ' €');
            $('#showUser').text(order.user.name);
            $('#showOrderStatus').text(order.status_name);
            $('#showReceivedDate').text(order.recevieved_date);
            $('#showNotice').html(order.notice);

            // Populate product images
            var productImagesContainer = $('#modalProductImages');
            productImagesContainer.empty();

            // Ensure productImages is an array before using forEach
            if (Array.isArray(productImages)) {
                productImages.forEach(function(image) {
                    productImagesContainer.append('<img data-src="storage/' + image +
                        '" class="lazyload img-fluid mb-2" style="max-width: 80%;">');
                });
            } else {
                productImagesContainer.append('<p>No images available</p>');
            }

            // Show the modal
            $('#orderDetailsModal').modal('show');
        });

        // show cancel modal
        $('.cancelOrder').click(function() {
            var order = $(this).data('order');
            console.log(order);
            $('#orderCancelId').val(order.id);
            $('#CancelcustomerName').text(order.customer.name);
            $('#CancelcustomerPhone').text(order.customer.phone);
            $('#CancelcustomerWeight').text(order.weight + ' g');
            $('#CancelcustomerCost').text(order.cost + ' €');
            $('#CancelcustomerStatus').text(order.status_name);
            $('#CancelcustomerNotice').html(order.notice);
        })

        // show recive modal
        $('.reciveOrder').click(function() {
            var order = $(this).data('order');
            console.log(order);
            $('#orderReciveId').val(order.id);
            $('#recivecustomerName').text(order.customer.name);
            $('#recivecustomerPhone').text(order.customer.phone);
            $('#recivecustomerWeight').text(order.weight + ' g');
            $('#recivecustomerCost').text(order.cost + ' €');
            $('#recivecustomerStatus').text(order.status_name);
            $('#recivecustomerNotice').html(order.notice);
            $('#firstCost').val(order.cost);
            // $('#reciveOrder').modal('show');
        })
        var debounceTimer;
        $('#customer_name').on('input', function() {
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
                        //   والاسم موجود بالفعل
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
    </script>
@endsection
