@extends('layouts.app')

@section('content')
@if (Auth::user()->type != 3)
<button class="btn btn-success mb-2" data-toggle="modal" data-target="#addBoxForm"><i class="fas fa-plus"></i></button>
    
@endif
    <div class="alert alert-success d-none" id="success-alert"></div>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>الصندوق</th>
                <th>المدخل</th>
                <th>التاريخ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($box as $b)
                <tr>
                    <td>{{ ($box->currentPage() - 1) * $box->perPage() + $loop->iteration }}</td>
                    <td>{{ $b->opened_box }}</td>
                    <td>{{ $b->user->name }}</td>
                    <td>{{ $b->created_at }}</td>
                    <td></td>
                </tr>
            @endforeach

        </tbody>
    </table>
    <div id="paginate">
        {{ $box->appends(request()->query())->links() }}
    </div>
    <!-- Insert Modal -->
    <div class="modal fade" id="addBoxForm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">إضافة صندوق </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="fullName">قيمة الصندوق</label>
                            <input type="text" id="opened_box" class="form-control" autocomplete="off" required>
                            <p id="box-error" class="text-danger"></p>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            //set autofocus to caliber name field (add form)
            $("#addBoxForm").on('shown.bs.modal', function() {
                $(this).find('#opened_box').focus();
                $("#opened_box").val('');
                $('#box-error').text('');
            });
        });

        $("#saveButton").click(function() {
                var opened_box = $("#opened_box").val();
                $(this).prop("disabled", true);
                // Send AJAX request
                @if (Auth()->user()->type == 1)
                $.ajax({
                    url: "{{ route('box.store') }}",
                    method: "POST",
                    data: {
                        opened_box,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Handle the response from the server
                        // Close the modal
                        $("#addBoxForm").modal("hide");
                        $('#success-alert').text(response.message).removeClass('d-none')
                        $('#saveButton').prop("disabled", false);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        // Handle the error, if any
                        $('#box-error').text(xhr.responseJSON.message);
                        $('#saveButton').prop("disabled", false);

                    }
                });
                @else
                $.ajax({
                    url: "{{ route('box.entry.store') }}",
                    method: "POST",
                    data: {
                        opened_box,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Handle the response from the server
                        // Close the modal
                        $("#addBoxForm").modal("hide");
                        $('#success-alert').text(response.message).removeClass('d-none')
                        $('#saveButton').prop("disabled", false);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        // Handle the error, if any
                        $('#box-error').text(xhr.responseJSON.message);
                        $('#saveButton').prop("disabled", false);

                    }
                });
                @endif
                
            });
    </script>
@endsection
