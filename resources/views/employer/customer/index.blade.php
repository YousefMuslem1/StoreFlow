@extends('employer.layouts.app')
@section('styles')
    <style>
        .highlight-row {
            background-color: yellow;
        }

        .success-message {
            display: none;
            color: green;
            font-weight: bold;
        }

        .status-completed {
            color: green;
        }

        .status-not-completed {
            color: red;
        }
    </style>
@endsection
@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"> إدارة الاٌقساط </h1>
    </div>
    <hr>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>العميل</th>
                    <th>منتجات مشتراه</th>
                    <th>السعر الإجمالي</th>
                    <th>مدفوع</th>
                    <th>متبقي</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->total_products }}</td>
                        <td>{{ $customer->total_selled_price }} €</td>
                        <td>{{ $customer->total_payments }} €</td>
                        <td>{{ $customer->remaining_amount }} €</td>
                        <td>
                            <!-- Button to trigger modal -->
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailsModal"
                                data-customer-id="{{ $customer->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
  
    {{ $customers->links() }}

    <!-- Modal -->
    <div class=" modal  fade " id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">تفاصيل دفعات</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>رمز المنتج</th>
                                    <th>المبيع</th>
                                    <th>مدفوع</th>
                                    <th>الوزن</th>
                                    <th>الصنف</th>
                                    <th>الحالة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="modal-details-body">
                                <!-- Details will be appended here via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <nav>
                        <ul class="pagination" id="modal-pagination">
                            <!-- Pagination links will be appended here via AJAX -->
                        </ul>
                    </nav>
                    <!-- Payment Form -->
                    <form id="payment-form" style="display: none;">
                        <div class="form-group">
                            <label for="amount">Payment Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </form>
                    <div id="success-message" class="success-message"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://cdn.rtlcss.com/bootstrap/v4.5.3/js/bootstrap.bundle.min.js" integrity="sha384-40ix5a3dj6/qaC7tfz0Yr+p9fqWLzzAXiwxVLt9dw7UjQzGYw6rWRhFAnRapuQyK" crossorigin="anonymous"></script>


    <script>
        $(document).ready(function() {
            $('#detailsModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var customerId = button.data('customer-id');

                loadInstallmentDetails(customerId, 1);
            });

            function loadInstallmentDetails(customerId, page) {
                $.ajax({
                    method: 'GET',
                    url: '/customer/installments/' + customerId + '?page=' + page,
                    success: function(response) {
                        var tbody = $('#modal-details-body');
                        tbody.empty();

                        // Sort the response data so "Not Completed" items come first
                        response.data.sort(function(a, b) {
                            var aStatus = (a.total_paid < a.product.selled_price) ? 1 : 0;
                            var bStatus = (b.total_paid < b.product.selled_price) ? 1 : 0;
                            return aStatus - bStatus;
                        });

                        response.data.forEach(function(installment) {
                            var product = installment.product;
                            var totalPaid = installment.total_paid;
                            var status = (parseFloat(totalPaid) >= parseFloat(product.selled_price)) ? 'Completed' :
                                'Not Completed';
                            var statusClass = (status === 'Completed') ? 'status-completed' :
                                'status-not-completed';
                            var transStatus = (parseFloat(totalPaid) >= parseFloat(product.selled_price)) ? 'مكتمل' :
                                'غير مكتمل';
                            var row = '<tr id="product-row-' + product.id + '">' +
                                '<td>' + product.short_ident + '</td>' +
                                '<td>' + product.selled_price + ' € </td>' +
                                '<td id="total-paid-' + product.id + '">' + totalPaid +
                                ' € </td>' +
                                '<td>' + product.weight + '</td>' + // Display weight
                                '<td>' + product.type.name + '</td>' + // Display type name
                                '<td class="' + statusClass + '">' + transStatus + '</td>';

                            // Only append the "Add Payment" button if the status is not "Completed"
                            if (status !== 'Completed') {
                                row += '<td>' +
                                    '<button type="button" class="btn btn-success btn-sm add-payment" data-product-id="' +
                                    product.id + '" data-customer-id="' + customerId + '">' +
                                    'دفعة' +
                                    '</button>' +
                                    '</td>';
                            } else {
                                row +=
                                    '<td></td>'; // Add an empty column if the status is "Completed"
                            }

                            row += '</tr>';

                            tbody.append(row);
                        });


                        var pagination = $('#modal-pagination');
                        pagination.empty();

                        if (response.links) {
                            response.links.forEach(function(link) {
                                if (link.label !== 'pagination.next' && link.label !==
                                    'pagination.previous') {
                                    var pageLink = '<li class="page-item' + (link.active ?
                                            ' active' : '') + '">' +
                                        '<a class="page-link" href="#" data-customer-id="' +
                                        customerId + '" data-page="' + link.label + '">' + link
                                        .label + '</a>' +
                                        '</li>';
                                    pagination.append(pageLink);
                                }
                            });
                        }

                        // Handle add payment button click
                        $('.add-payment').off('click').on('click', function() {
                            var productId = $(this).data('product-id');
                            var customerId = $(this).data('customer-id');

                            // Highlight the selected row
                            $('tr').removeClass('highlight-row');
                            $('#product-row-' + productId).addClass('highlight-row');

                            // Show the payment form
                            $('#payment-form').show();

                            // Set up the form submission
                            $('#payment-form').off('submit').on('submit', function(event) {
                                event.preventDefault();

                                var amount = $('#amount').val();

                                $.ajax({
                                    method: 'POST',
                                    url: '/customer/add-payment',
                                    data: {
                                        product_id: productId,
                                        customer_id: customerId,
                                        amount_paid: amount,
                                        _token: "{{ csrf_token() }}"
                                    },
                                    success: function(response) {
                                        console.log('Payment added:',
                                            response);
                                        // Update the total paid amount in the table
                                        var totalPaid = parseFloat($(
                                                '#total-paid-' +
                                                productId).text()) +
                                            parseFloat(amount);
                                        $('#total-paid-' + productId).text(
                                            totalPaid.toFixed(2));
                                        $('#amount').val(
                                            ''); // Clear the input
                                        $('#payment-form')
                                            .hide(); // Hide the form

                                        // Update the status
                                        var productSelledPrice = parseFloat(
                                            $('#product-row-' +
                                                productId +
                                                ' td:nth-child(2)')
                                            .text());
                                        if (totalPaid >=
                                            productSelledPrice) {
                                            $('#product-row-' + productId +
                                                    ' td:nth-child(4)')
                                                .text('Completed')
                                                .removeClass(
                                                    'status-not-completed')
                                                .addClass(
                                                    'status-completed');
                                        }

                                        // Remove the highlight from the row
                                        $('#product-row-' + productId)
                                            .removeClass('highlight-row');

                                        // Show the success message
                                        $('#success-message').text(
                                                'Payment added successfully!'
                                            ).show().delay(3000)
                                            .fadeOut();
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('Error:', error);
                                        // Handle any errors that occur during the request
                                    }
                                });
                            });
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        // Handle any errors that occur during the request
                    }
                });
            }

            $(document).on('click', '#modal-pagination .page-link', function(event) {
                event.preventDefault();
                var customerId = $(this).data('customer-id');
                var page = $(this).data('page');
                loadInstallmentDetails(customerId, page);
            });
        });
    </script>
@endsection
