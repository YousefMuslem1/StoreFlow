@extends('employer.layouts.app')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">إدارة العربون </h1>
    </div>
    <hr>

    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    @if (session('order_delivered'))
        @php
            $order = session('order_delivered');
        @endphp
        <div class="alert alert-success">تم تسليم طلب العميل {{ $order->customer->name }} </div>
    @endif

    <a href="{{ route('orderes.create') }}" class="btn btn-success mb-2">إضافة عربون <i class="fas fa-plus"></i></a>

    <!-- Search and Filter Form -->
    <form action="{{ route('orderes.index') }}" method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-3 mt-1">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="البحث باسم العميل" autocomplete="off">
            </div>
            <div class="col-md-3 mt-1">
                <select name="status" class="form-control">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- <div class="col-md-3">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control" placeholder="من تاريخ">
            </div>
            <div class="col-md-3">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control" placeholder="إلى تاريخ">
            </div> --}}
            <div class="col-md-3 mt-1">
                <button type="submit" class="btn btn-primary">بحث</button>
                <a href="{{ route('orderes.index') }}" class="btn btn-secondary">تحديث</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <th>#</th>
                <th>العميل</th>
                <th>رقم الطلب</th>
                <th>مدفوع</th>
                <th>المنتج</th>
                <th>المسؤول</th>
                <th>الحالة</th>
                <th>تاريخ الطلب</th>
                <th></th>
            </thead>
            @php
                $status = ['', 'warning', 'success', 'danger', 'primary'];
            @endphp
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->customer->name }}</td>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->amount_paid }} <b>€</b></td>
                        <td>
                            @if ($order->product)
                                <a href="{{ route('products.getProduct', $order->product->id) }}">معاينة</a>
                            @endif
                        </td>
                        <td>{{ $order->user->name ?? '' }}</td>
                        <td><span class="badge badge-{{ $status[$order->status] }} p-2">{{ $order->status_name }}</span>
                        </td>
                        <td>{{ $order->created_at }}</td>
                        <td class="d-flex">
                            <button class="btn btn-primary btn-sm " data-toggle="modal" data-target="#orderDetails"
                                data-customer-name="{{ $order->customer->name  }}"
                                data-amount-paid = "{{ $order->amount_paid }}"
                                data-related-product = "{{ $order->product->id ?? '#' }}"
                                data-user = "{{ $order->user->name ?? ''}}"
                                data-customer-phone = "{{ $order->customer->phone }}"
                                data-order-delevired-date = "{{ $order->received_date }}"
                                data-created-at = "{{ $order->created_at }}" data-notice = "{{ $order->notice }}"
                                data-status = "{{ $order->status_name }}"
                                data-status-num = "{{ $order->status }}"><i class="fas fa-eye"></i></button>

                            @if ($order->status !== '3')
                                <button class="btn btn-danger btn-sm mx-1 cancel-button" data-toggle="modal"
                                    data-target="#cancelModal" data-order-id="{{ $order->id }}"
                                    data-customer-name="{{ $order->customer->name }}" data-notice="{{ $order->notice }}"
                                    data-amount-paid = "{{ $order->amount_paid }}">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            @endif

                            @if ($order->status == '1')
                                <button class="btn btn-success btn-sm deliver-button" data-toggle="modal"
                                    data-order-id="{{ $order->id }}" data-target="#deliverModal">
                                    <i class="fas fa-shipping-fast"></i>
                                </button>
                            @endif

                            @if ($order->status == '4')
                                <button class="btn btn-success btn-sm deliver-button" data-toggle="modal"
                                    data-order-id="{{ $order->id }}" data-target="#deliverBookedModal"
                                    data-product-selled-price = "{{ $order->product->selled_price }}"
                                    data-amount-paid = "{{ $order->amount_paid }}">
                                    <i class="fas fa-shipping-fast"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $orders->links() }}
    </div>

    <!-- Modal (same as before) -->
    <!-- Modal -->
    <div class="modal fade" id="orderDetails" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th>إسم العميل</th>
                                            <td id="customerNameModal"></td>
                                        </tr>
                                        <tr>
                                            <th>هاتف العميل</th>
                                            <td id="phone"></td>
                                        </tr>
                                        <tr>
                                            <th>المبلغ المدفوع</th>
                                            <td id="amountPaid"></td>
                                        </tr>
                                        <tr>
                                            <th>المنتج المرتبط</th>
                                            <td id="relatedProduct"></td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ التسليم</th>
                                            <td id="deleviredDate"></td>
                                        </tr>
                                        <tr>
                                            <th>الحالة</th>
                                            <td id="status"></td>
                                        </tr>
                                        <tr>
                                            <th>المسؤول</th>
                                            <td id="user"></td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الإنشاء</th>
                                            <td id="created"></td>
                                        </tr>
                                        <tr>
                                            <th>وصف الطلب</th>
                                            <td id="notice"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">إلغاء طلب</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="customerNameCancel">إسم العميل: </label>
                    <span id="customerNameCancel"></span>
                    <hr>
                    <label for="amountPaidCancel">المبلغ المدفوع</label>
                    <span id="amountPaidCancel"></span>
                    <hr>
                    <label for="noticeCancel">وصف الطلب: </label>
                    <span id="noticeCancel"></span>
                    <hr>
                    <form action="{{ route('orderes.cancel') }}" method="POST" id="cancelForm">
                        @csrf
                        <div class="form-group">
                            <label for="cancelReason">سبب الإلغاء</label>
                            <input type="hidden" id="orderIdCancel" value="">
                            <textarea class="form-control" id="cancelReason" name="cancel_reason" rows="3" required></textarea>
                        </div>
                        <input type="hidden" id="orderId" name="orderId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-danger" id="confirmCancel">تأكيد الإلغاء</button>
                </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Deliver Modal -->
    <div class="modal fade" id="deliverModal" tabindex="-1" role="dialog" aria-labelledby="deliverModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">تسليم طلب</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('orderes.deliver') }}" method="POST" id="deliverForm">
                        @csrf
                        <div class="form-group">
                            <label for="weight">الوزن</label>
                            <input type="number" step="0.01" class="form-control" id="weight" name="weight"
                                required>
                            <input type="hidden" id="orderId" name="order_id">
                        </div>
                        <div class="form-group">
                            <label for="caliber">العيار</label>
                            <select name="caliber" id="caliber" class=" type-select custom-select"
                                style="width: 100%">
                                <option value="">----إختر من هنا----</option>
                                @foreach ($calibers as $caliber)
                                    <option value="{{ $caliber->id }}">{{ $caliber->full_name }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="form-group">
                            <label for="type">النوع</label>
                            <select name="type" id="type" class=" type-select custom-select"
                                style="width: 100%">
                                <option value="">----إختر من هنا----</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="selled_price">سعر المبيع</label>
                            <input type="number" step="0.1" id="selledPrice" name="selled_price"
                                class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="selled_price">ملاحظة</label>
                            <textarea name="notice" id="deliverNotice" cols="30" rows="2" class="form-control"></textarea>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-danger" id="confirmDeleviry"> تأكيد التسليم</button>
                </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Deliver Booked Modal -->
    <div class="modal fade" id="deliverBookedModal" tabindex="-1" role="dialog" aria-labelledby="deliverModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">تسليم طلب</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>سعر المبيع</th>
                                <td id="selled_price"></td>
                            </tr>
                            <tr>
                                <th>مدفوع</th>
                                <td id="paid"></td>
                            </tr>
                            <tr>
                                <th>باقي</th>
                                <td id="remaining"></td>
                            </tr>
                        </tbody>
                    </table>
                    <form action="{{ route('orderes.deliver') }}" method="POST" id="deliverForm">
                        @csrf
                        <div class="form-group">
                            <label for="selled_price">ملاحظة</label>
                            <textarea name="notice" id="deliverNotice" cols="30" rows="2" class="form-control"></textarea>
                            <input type="hidden" id="orderBookedId" name="order_id" value="">
                            <input type="hidden" name="booked" value="true">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                    <button type="submit" class="btn btn-danger" id="confirmDeleviry"> تأكيد التسليم</button>
                </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.type-select').select2();
        });

        $('#cancelForm').on('submit', function(e) {
            $('#confirmCancel').prop('disabled', true);
        });

        $('#deliverModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var orderId = button.data('order-id');
            var modal = $(this);
            modal.find('#orderId').val(orderId);
        });

        $('#deliverBookedModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var orderId = button.data('order-id');
            var product_selled_price = button.data('product-selled-price');
            var product_amount_paid = button.data('amount-paid');
            var remaining = product_selled_price - product_amount_paid;

            var modal = $(this);
            modal.find('#orderBookedId').val(orderId);
            modal.find('#selled_price').text(product_selled_price + ' €');
            modal.find('#paid').text(product_amount_paid + ' €');
            modal.find('#remaining').text(remaining + ' €');
        });

        $('#deliverForm').on('submit', function(e) {
            e.preventDefault();
            var weight = $('#weight').val();
            var caliber = $('#caliber').val();
            var type = $('#type').val();
            var selled_price = $('#selledPrice').val();
            $('#confirmDeleviry').prop('disabled', true);
            if (weight === '' || caliber === '' || type === '' || selled_price === '') {
                alert('جميع الحقول مطلوبة');
                $('#confirmDeleviry').prop('disabled', false);
                return;
            } else {
                this.submit();
            }
        });

        $('#cancelModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var orderId = button.data('order-id');
            var customerNameCancel = button.data('customer-name');
            var notice = button.data('notice');
            var amountPaid = button.data('amount-paid');

            var modal = $(this);
            modal.find('.modal-body #customerNameCancel').text(customerNameCancel);
            modal.find('.modal-body #noticeCancel').html(notice);
            modal.find('.modal-body #amountPaidCancel').text(amountPaid + ' €');
            modal.find('#orderId').val(orderId);
        });

        $('#orderDetails').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var customer_name = button.data('customer-name');
            var amountPaid = button.data('amount-paid');
            var relatedProduct = button.data('related-product');
            var user = button.data('user');
            var phone = button.data('customer-phone');
            var created = button.data('created-at');
            var notice = button.data('notice');
            var status = button.data('status');
            var status_num = button.data('status-num');
            var delevireddate = button.data('order-delevired-date');
            // Generate the URL
            var productUrl = '/sell/products/' + relatedProduct;
            // Create the link element

            if (status_num == '2' || status_num == '4') {
            console.log(status_num);

                var productLink = $('<a></a>')
                    .attr('href', productUrl)
                    .text('معاينة')
                    .addClass('btn btn-link'); // Optional: Add a class for styling
                $('#relatedProduct').empty().append(productLink);
            } else {
                // Clear the related product link if status is not 2 or 4
                $('#relatedProduct').empty();
            }

            // Update the modal's content
            var modal = $(this);
            modal.find('.modal-title').text('معلومات طلب العميل ' + customer_name);
            modal.find('.modal-body #customerNameModal').text(customer_name);
            modal.find('.modal-body #amountPaid').text(amountPaid + ' €');
            modal.find('.modal-body #user').text(user);
            modal.find('.modal-body #phone').text(phone);
            modal.find('.modal-body #created').text(created);
            modal.find('.modal-body #notice').html(notice);
            modal.find('.modal-body #delevireddate').text(delevireddate);
            modal.find('.modal-body #status').text(status);
        });
    </script>
@endsection
