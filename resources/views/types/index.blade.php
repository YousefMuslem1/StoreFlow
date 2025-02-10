@extends('layouts.app')

@section('content')
    @if (auth()->user()->type == 1)
        <button class="btn btn-success" data-toggle="modal" data-target="#addNewTypeForm"><i class="fa fa-plus"></i>
            {{ __('buttons.add') }}</button>
    @endif
    <table class="table my-3">
        <thead>
            <tr>
                <th>#</th>
                <th>الصنف</th>
                <th>النوع</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($types as $type)
                <tr>
                    <td>{{ ($types->currentPage() - 1) * $types->perPage() + $loop->iteration }}</td>
                    <td>{{ $type->name }}</td>
                    <td class="badge badge-pill badge-sm badge-{{ $type->is_quantity ? 'success' : 'warning' }}">
                        {{ $type->is_quantity ? 'كميّة' : 'مفرد' }}</td>
                    <td>
                        {{-- <button class="btn btn-danger btn-sm delete-button" data-record-id="{{ $type->id }}"
                            data-record-value="{{ $type->name }}">{{ __('buttons.delete') }}</button> --}}
                        @if (auth()->user()->type == 1)
                            <button class="btn btn-primary btn-sm edit-button" data-toggle="modal" data-target="#editTypeForm"
                                data-record-id="{{ $type->id }}"
                                data-record-name="{{ $type->name }}">{{ __('buttons.edit') }}</button>
                        @endif
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
    {{ $types->links() }}


    <!-- Insert Modal -->
    <div class="modal fade" id="addNewTypeForm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('types.add_type') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">{{ __('types.type') }}</label>
                            <input type="text" id="name" class="form-control" placeholder="خاتم" autocomplete="off"
                                required>
                            <p id="name-error" class="text-danger"></p>
                        </div>
                        <div class="form-group">
                            <label for="isQuantity">النوع:</label>
                            <select class="form-control" id="isQuantity" name="is_quantity">
                                <option value="0">مفرد</option>
                                <option value="1">كميّة</option>
                            </select>
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
    <div class="modal fade" id="editTypeForm" tabindex="-1" role="dialog" aria-hidden="true">
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
                            <label for="type">{{ __('types.type') }}</label>
                            <input type="hidden" id="editRecordId" class="form-control" readonly>
                            <input type="text" id="editRecordName" value="" class="form-control"
                                placeholder="Z.b: 21" autocomplete="off" required>
                            <p id="value-error" class="text-danger"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('buttons.close') }}</button>
                        <button type="button" class="btn btn-primary" id="updateRecord">{{ __('buttons.save') }}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            //set autofocus to type name field
            $("#addNewTypeForm").on('shown.bs.modal', function() {
                $(this).find('#name').focus();
                $('#name-error').text('');

            });
            //set autofocus to type name field (edit form)
            $("#editTypeForm").on('shown.bs.modal', function() {
                $(this).find('#editRecordName').focus();

                $('#name-error').text('');

            });
            // Handle "Save" button click
            $("#saveButton").click(function() {

                var type = $("#name").val();
                var is_quantity = $("#isQuantity").val();
                $(this).prop("disabled", true);
                // Send AJAX request
                $.ajax({
                    url: "{{ route('types.store') }}",
                    method: "POST",
                    data: {
                        type,
                        is_quantity,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Handle the response from the server

                        // Close the modal
                        $("#addNewTypeForm").modal("hide");
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
                    title: ' {{ __('types.type') }} ' + value,
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
                            url: "/types/" + recordId,
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
                $("#editRecordId").val(recordId);
                $("#editRecordName").val(recordName);
            });
            $("#updateRecord").click(function() {
                var recordId = $("#editRecordId").val();
                var updatedName = $("#editRecordName").val();
                $(this).prop("disabled", true);
                // Send an AJAX request to update the record
                $.ajax({
                    url: "/types/" + recordId,
                    method: "PUT", // Adjust the HTTP method to match your update route
                    data: {
                        value: updatedName,
                        id: recordId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Handle the success response
                        location.reload();
                        // Close the modal or update the record on the page
                        $("#editTypeForm").modal("hide");
                    },
                    error: function(xhr, status, error) {
                        // Handle the error, if any
                        $('#value-error').text(xhr.responseJSON.message);
                        $("#updateRecord").prop("disabled", false);
                        console.log(xhr.responseJSON.message);
                    }
                });
            });
        });
    </script>
@endsection
