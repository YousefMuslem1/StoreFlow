@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>إدارة الموردين</h1>
        <hr>
        <button type="button" class="btn btn-success mb-2" data-toggle="modal" data-target="#addSupplierModal">
            <i class="fas fa-plus"></i> مورد جديد
        </button>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>اسم المورد</th>
                    <th>المال المثبت</th>
                    <th>المال غير المثبت</th>
                    <th>الذهب المثبت</th>
                    <th>الذهب غير المثبت</th>
                    <th>التراكمي</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->supplierBalance->fixed_money ?? '0' }} €</td>
                        <td>{{ $supplier->supplierBalance->unfixed_money ?? '0' }} €</td>
                        <td>{{ $supplier->supplierBalance->fixed_gold ?? '0' }} غرام</td>
                        <td>{{ $supplier->supplierBalance->unfixed_gold ?? '0' }} غرام</td>
                        <td>
                            @if (($supplier->supplierBalance->fixed_gold ?? 0) < 0)
                                لنا عليه {{ $supplier->supplierBalance->fixed_gold }} غرام ذهب
                            @elseif(($supplier->supplierBalance->fixed_gold ?? 0) > 0)
                                له علينا {{ abs($supplier->supplierBalance->fixed_gold) }} غرام ذهب
                            @else
                                لا يوجد دين
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="openTransactionModal({{ $supplier->id }}, '{{ $supplier->name }}')">
                                <i class="fas fa-plus"></i>
                            </button>
                            <a href="{{ route('suppliers.transactions', $supplier->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- عرض pagination links -->
        <div class="d-flex justify-content-center">
            {{ $suppliers->links() }}
        </div>
    </div>

    <!-- Modal for adding a new supplier -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addSupplierForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSupplierModalLabel">إضافة مورد جديد</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="supplierName">اسم المورد</label>
                            <input type="text" class="form-control" id="supplierName" name="name" autocomplete="off" required>
                        </div>
                        <div class="form-group">
                            <label for="supplierContact">معلومات الاتصال</label>
                            <textarea id="supplierContact" class="form-control" name="contact_info" cols="30" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for adding a transaction -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addTransactionForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTransactionModalLabel">إضافة عملية</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="supplierId" name="supplier_id">
                        <div class="form-group">
                            <label>اسم المورد:</label>
                            <span id="supplierNameModal"></span>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>نوع العملية</label><br>
                            <label><input type="radio" name="type" value="1" checked> مال</label>
                            <label><input type="radio" name="type" value="2"> ذهب</label>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>نوع الحركة</label><br>
                            <label><input type="radio" name="transaction_type" value="1" checked> إرسال</label>
                            <label><input type="radio" name="transaction_type" value="2"> استلام</label>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label for="amount">المبلغ</label>
                            <input type="number" class="form-control" id="amount" name="amount" required>
                        </div>
                        <div class="form-group">
                            <label for="price_per_gram">سعر التثبيت</label>
                            <input type="number" class="form-control" id="price_per_gram" name="price_per_gram">
                        </div>
                        <div class="form-group">
                            <label for="expected_weight">الوزن المتوقع</label>
                            <input type="number" class="form-control" id="expected_weight" name="expected_weight" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">تأكيد العملية</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Display transaction summary here -->
                    <p><strong>اسم المورد:</strong> <span id="confirmSupplierName"></span></p>
                    <p><strong>النوع:</strong> <span id="confirmTransactionType"></span></p>
                    <p><strong>الحركة:</strong> <span id="confirmTransactionAction"></span></p>
                    <p><strong>الكمية:</strong> <span id="confirmAmount"></span></p>
                    <p><strong>سعر الذهب:</strong> <span id="confirmPricePerGram"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="confirmSubmit">تأكيد</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // إضافة مورد جديد
        $('#addSupplierForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            // Disable the save button to prevent multiple submissions
            $('#addSupplierForm button[type="submit"]').prop('disabled', true);

            // Clear previous errors
            $('#addSupplierForm .text-danger').remove();

            $.ajax({
                url: '{{ route('suppliers.store') }}', // Ensure you have a route named suppliers.store
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#addSupplierModal').modal('hide');
                        alert('تم إضافة المورد بنجاح: ' + response.name);
                        location.reload(); // Refresh the page
                    } else {
                        alert('حدث خطأ أثناء إضافة المورد.');
                        $('#addSupplierForm button[type="submit"]').prop('disabled', false);
                    }
                },
                error: function(error) {
                    // Handle validation errors
                    if (error.status === 422) { // Laravel validation error status
                        let errors = error.responseJSON.errors;
                        for (let field in errors) {
                            $('#addSupplierForm input[name=' + field + ']').after(
                                '<span class="text-danger">' + errors[field][0] +
                                '</span>');
                        }
                    } else {
                        alert('حدث خطأ أثناء إضافة المورد.');
                    }

                    // Re-enable the save button
                    $('#addSupplierForm button[type="submit"]').prop('disabled', false);
                }
            });
        });

        // Open the modal and set supplier information
        window.openTransactionModal = function(supplierId, supplierName) {
            $('#supplierId').val(supplierId);
            $('#supplierNameModal').text(supplierName);
            $('#addTransactionModal').modal('show');
        };

        // Calculate expected gold weight when price per gram is entered
        $('#price_per_gram, #amount').on('input', function() {
            let amount = parseFloat($('#amount').val());
            let pricePerGram = parseFloat($('#price_per_gram').val());
            if (!isNaN(amount) && !isNaN(pricePerGram) && pricePerGram > 0) {
                let expectedWeight = amount / pricePerGram;
                $('#expected_weight').val(expectedWeight.toFixed(2));
            } else {
                $('#expected_weight').val('');
            }
        });

        // Handle add transaction form submission
        $('#addTransactionForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the form from submitting immediately

            // Capture form data
            let supplierName = $('#supplierNameModal').text();
            let transactionType = $('input[name="type"]:checked').parent().text().trim();
            let transactionAction = $('input[name="transaction_type"]:checked').parent().text().trim();
            let amount = $('#amount').val();
            let pricePerGram = $('#price_per_gram').val() || 'غير متاح';

            // Display the captured data in the confirmation modal
            $('#confirmSupplierName').text(supplierName);
            $('#confirmTransactionType').text(transactionType);
            $('#confirmTransactionAction').text(transactionAction);
            $('#confirmAmount').text(amount);
            $('#confirmPricePerGram').text(pricePerGram);

            // Show the confirmation modal
            $('#confirmationModal').modal('show');
        });

        // Event listener for the confirmation button in the confirmation modal
        $('#confirmSubmit').on('click', function() {
            // Disable the confirm button to prevent multiple submissions
            $(this).prop('disabled', true);

            // Submit the form after confirmation
            let formData = $('#addTransactionForm').serialize();

            $.ajax({
                url: '{{ route('supplierTransactions.store') }}',
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#confirmationModal').modal('hide');
                    alert('تمت إضافة العملية بنجاح.');
                    location.reload(); // Refresh the page
                },
                error: function(error) {
                    alert('حدث خطأ أثناء إضافة العملية.');
                    // Re-enable the confirm button in case of an error
                    $('#confirmSubmit').prop('disabled', false);
                }
            });
        });
        // Clear the form fields when the modal is closed
        $('#addTransactionModal').on('hidden.bs.modal', function() {
            $('#addTransactionForm')[0].reset();
            $('#expected_weight').val(''); // Clear any calculated values
        });

        $('#confirmationModal').on('hidden.bs.modal', function() {
            // Reset the confirm button
            $('#confirmSubmit').prop('disabled', false);
        });
    });
</script>


@endsection
