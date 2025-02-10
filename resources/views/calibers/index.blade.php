@extends('layouts.app')

@section('content')
    @if (auth()->user()->type == 1)
        <button class="btn btn-success" data-toggle="modal" data-target="#addNewCaliberForm"><i class="fa fa-plus"></i>
            {{ __('buttons.add') }}</button>
    @endif

    <table class="table my-3">
        <thead>
            <tr>
                <th>#</th>
                <th>نوع العيار</th>
                <th>{{ __('caliber.caliber') }}</th>
                <th>سعر الغرام</th>
                <th>قيمة التحويل</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($calibers as $caliber)
                <tr>
                    <td>{{ ($calibers->currentPage() - 1) * $calibers->perPage() + $loop->iteration }}</td>
                    <td>{{ $caliber->full_name }}</td>
                    <td>{{ $caliber->name }}</td>
                    <td>{{ $caliber->caliber_price }}</td>
                    <td>{{ $caliber->transfarmed }}</td>
                    <td>
                        {{-- <button class="btn btn-danger btn-sm delete-button" data-record-id="{{ $caliber->id }}"
                            data-record-value="{{ $caliber->name }}">{{ __('buttons.delete') }}</button> --}}
                        @if (auth()->user()->type == 1)
                            <button class="btn btn-primary btn-sm edit-button" data-toggle="modal"
                                data-target="#editCaliberForm" data-record-id="{{ $caliber->id }}"
                                data-record-name="{{ $caliber->name }}"
                                data-record-transfarmed = "{{ $caliber->transfarmed }}"
                                data-record-fullname = "{{ $caliber->full_name }}"
                                data-record-price="{{ $caliber->caliber_price }}">{{ __('buttons.edit') }}</button>
                        @endif
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
    {{ $calibers->links() }}
    <!-- Insert Modal -->
    <div class="modal fade" id="addNewCaliberForm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('caliber.add_caliber') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fullName">الاسم</label>
                            <input type="text" id="fullName" class="form-control" placeholder="عيار 21 خشر"
                                autocomplete="off" required>
                            <p id="name-error" class="text-danger"></p>
                        </div>
                        <div class="form-group">
                            <label for="name">{{ __('caliber.caliber') }}</label>
                            <input type="number" id="name" class="form-control" placeholder="Z.b: 21"
                                autocomplete="off" required>
                            <p id="name-error" class="text-danger"></p>
                        </div>
                        <div class="form-group">
                            <label for="caliberPrice">سعر غرام العيار</label>
                            <input type="number" step="0.01" id="caliberPrice" class="form-control"
                                placeholder="Z.b: 27.56" autocomplete="off" required>
                            {{-- <p id="name-error" class="text-danger"></p> --}}
                        </div>
                        <div class="form-group">
                            <label for="name">القيمة التحويلية</label>
                            <input type="number" step="0.01" id="transfarValue" class="form-control"
                                placeholder="Z.b: 0.875" autocomplete="off" required>
                            <p id="name-error" class="text-danger"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('buttons.close') }}</button>
                        <button type="submit" class="btn btn-primary" id="saveButton">{{ __('buttons.save') }}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- Edit Modal -->
    <div class="modal fade" id="editCaliberForm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('caliber.edit_caliber') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="hidden" id="editRecordId" class="form-control" readonly>
                            <div class="form-group">
                                <label for="EditFullName">العيار</label>
                                <input type="text" id="EditFullName" value="" class="form-control"
                                    placeholder="21 خشر" autocomplete="off" required>
                            </div>
                            <div class="form-group">
                                <label for="caliber">{{ __('caliber.caliber') }}</label>
                                <input type="number" id="editRecordName" value="" class="form-control"
                                    placeholder="Z.b: 21" autocomplete="off" required>
                            </div>
                            <p id="value-error" class="text-danger"></p>
                            <div class="form-group">
                                <label for="caliber">السعر</label>
                                <input type="number" id="editRecordPrice" value="" class="form-control"
                                    placeholder="Z.b: 33.45" autocomplete="off" required>
                            </div>
                            <div class="form-group">
                                <label for="caliber">القيمة التحويلية</label>
                                <input type="number" id="editRecordTransfarmed" value="" class="form-control"
                                    placeholder="Z.b: 0.45" autocomplete="off" required>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('buttons.close') }}</button>
                        <button type="button" class="btn btn-primary"
                            id="updateRecord">{{ __('buttons.save') }}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            //set autofocus to caliber name field (add form)
            $("#addNewCaliberForm").on('shown.bs.modal', function() {
                $(this).find('#fullName').focus();
                $('#name-error').text('');

            });
            //set autofocus to caliber name field (edit form)
            $("#editCaliberForm").on('shown.bs.modal', function() {
                $(this).find('#EditFullName').focus();
                $('#name-error').text('');
            });
            // Handle "Save" button click ( add model)
            $("#saveButton").click(function() {
                var caliber = $("#name").val();
                var price = $("#caliberPrice").val();
                var transfarmed = $("#transfarValue").val();
                var full_name = $("#fullName").val();
                $(this).prop("disabled", true);
                // Send AJAX request
                $.ajax({
                    url: "{{ route('calibers.store') }}",
                    method: "POST",
                    data: {
                        caliber,
                        price,
                        transfarmed,
                        full_name,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Handle the response from the server
                        // Close the modal
                        $("#addNewCaliberForm").modal("hide");
                        $('#saveButton').prop("disabled", false);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        // Handle the error, if any
                        $('#name-error').text(xhr.responseJSON.message);
                        $('#saveButton').prop("disabled", false);

                    }
                });
            });

            // Handle "Delete" button click
            $(".delete-button").click(function() {
                // Get the record ID from the data attribute
                var recordId = $(this).data("record-id");
                var value = $(this).data("record-value");
                $(this).prop("disabled", true);
                // Show the SweetAlert2 confirmation prompt
                Swal.fire({
                    title: ' {{ __('caliber.caliber') }} ' + value,
                    text: '{{ __('alert.delete_confirm_body') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('buttons.yes') }}',
                    cancelButtonText: '{{ __('buttons.cancel') }}',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // User clicked "Yes, delete it!"

                        // Send an AJAX request to delete the record
                        $.ajax({
                            url: "/calibers/" + recordId,
                            method: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                // Handle the success response
                                location.reload();
                                // You can also remove the row from the table
                            },
                            error: function(xhr, status, error) {
                                // Handle the error, if any
                                console.log(error);

                            }
                        });
                    } else {
                        $(this).prop("disabled", false);

                    }
                });
            });
            // Handle "Edit" button click
            $(".edit-button").click(function() {
                var recordId = $(this).data("record-id");
                var recordName = $(this).data("record-name");
                var recordPrice = $(this).data("record-price");
                var fullName = $(this).data("record-fullname");
                var recordTransfarmed = $(this).data("record-transfarmed");
                $("#editRecordId").val(recordId);
                $("#editRecordName").val(recordName);
                $("#editRecordPrice").val(recordPrice);
                $("#EditFullName").val(fullName);
                $("#editRecordTransfarmed").val(recordTransfarmed);
            });

            $("#updateRecord").click(function() {
                var recordId = $("#editRecordId").val();
                var updatedName = $("#editRecordName").val();
                var updatedPrice = $("#editRecordPrice").val();
                var fullname = $("#EditFullName").val();
                var updatedTransfarmed = $("#editRecordTransfarmed").val();
                $(this).prop("disabled", true);
                // Send an AJAX request to update the record
                $.ajax({
                    url: "/calibers/" + recordId,
                    method: "PUT", // Adjust the HTTP method to match your update route
                    data: {
                        value: updatedName,
                        id: recordId,
                        updatedPrice,
                        updatedTransfarmed,
                        fullname,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Handle the success response
                        location.reload();
                        // Close the modal or update the record on the page
                        $("#editModal").modal("hide");
                    },
                    error: function(xhr, status, error) {
                        // Handle the error, if any
                        $('#value-error').text(xhr.responseJSON.message);
                        // $('#price-error').text(xhr.responseJSON.message);
                        $("#updateRecord").prop("disabled", false);
                        console.log(xhr.responseJSON.message);
                    }
                });
            });
        });
    </script>
@endsection
