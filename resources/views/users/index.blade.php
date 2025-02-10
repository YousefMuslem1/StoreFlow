@extends('layouts.app')

@section('content')
    <button class="btn btn-success" data-toggle="modal" data-target="#addNewUserForm"><i class="fa fa-plus"></i>
        {{ __('buttons.add') }}</button>


    <table class="table my-3">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>الصلاحية</th>
                <th>الحالة</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @php
                $roles = ['', 'آدمن', 'موظف', 'مراقب', 'مدخل منتجات'];
            @endphp
            @foreach ($users as $user)
                @if ($user->id !== 1)
                    <tr>
                        <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $roles[$user->type] }}</td>

                        <td>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="post" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-{{ $user->status == 0 ? 'danger' : 'success' }} btn-sm "
                                    type="submit">{{ $user->status == 0 ? 'غير نشط ' : 'نشط' }}</button>
                            </form>
                        </td>
                    </tr>
                    @endif
                @endforeach

        </tbody>
    </table>
    <div id="paginate">
        {{ $users->appends(request()->query())->links() }}
    </div>
    <!-- Insert Modal -->
    <div class="modal fade" id="addNewUserForm" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">إضافة موظف جديد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">إسم المستخدم</label>
                            <input type="text" id="name" class="form-control" placeholder="" autocomplete="off"
                                required>
                            <label for="status" class="mt-2">الصلاحية</label>
                            <select name="status" id="status" class="form-control">
                                <option value="1">ادمن</option>
                                <option value="2">موظف</option>
                                <option value="3">مراقب</option>
                                <option value="4">مدخل منتجات</option>
                            </select>
                            <label for="password" class="mt-2">كلمة المرور</label>
                            <input type="text" id="password" class="form-control" placeholder="" autocomplete="off"
                                required>
                            <label for="name" class="mt-2"> تأكيد كلمة المرور</label>
                            <input type="text" id="password_confirmation" class="form-control" placeholder=""
                                autocomplete="off" required>
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
@endsection

@section('scripts')
    <script>
        // Handle "Save" button click
        $("#saveButton").click(function() {

            var name = $("#name").val();
            var password = $("#password").val();
            var password_confirmation = $("#password_confirmation").val();
            var status = $("#status").val();

            $(this).prop("disabled", true);
            // Send AJAX request
            $.ajax({
                url: "{{ route('users.store') }}",
                method: "POST",
                data: {
                    name,
                    password,
                    password_confirmation,
                    status,
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



        //set autofocus to type name field (edit form)
        $("#addNewUserForm").on('shown.bs.modal', function() {
            $(this).find('#name').focus();
        });


        function confirmDelete() {
            return confirm('هل انت متأكد من حذف المنتج');
        }
    </script>
@endsection
