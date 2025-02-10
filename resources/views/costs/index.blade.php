@extends('layouts.app')
@section('styles')
    <style>
        .table-container {
            overflow-x: auto;
        }

        @media (max-width: 576px) {
            #submitButton {
                margin-bottom: 20px;
                /* Adjust the margin-top value as needed */
            }
        }
    </style>
@endsection
@section('content')
    @if (Auth::user()->type != 3)
        <button class="btn btn-primary btn-sm " data-toggle="modal" data-target="#addNewCost"> جديد <i
                class="fas fa-plus"></i></button>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <hr>
    <div class="row">
        <div class="col-md-4">
            <form action="{{ Auth::user()->type == 4 ? route('costs.entry.index') : route('costs.index') }}" method="GET"
                class="form-inline">
                <div class="form-group">
                    <select name="cost_type_id" id="cost_type_id" class="form-control">
                        <option value="">---------الكل---------</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}"
                                {{ request('cost_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success">تنفيذ</button>
            </form>
        </div>
        @if (request('cost_type_id'))
            <div class="col-md-3">
                <div class="alert alert-secondary">
                    <p>المجموع الكلي لسحب: {{ $totalNegativeSum }} €</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="alert alert-warning">
                    <p>المجموع الكلي للايداع : {{ $totalPositiveSum }} €</p>
                </div>
            </div>
        @endif

    </div>


    <div class="table-container  mt-2">
        <div class="alert alert-success d-none" id="successAlert"></div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>العملية</th>
                    <th>المدخل</th>
                    <th>تاريخ الإنشاء</th>
                    <th>ملاحظة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($costs as $cost)
                    <tr>
                        <td>{{ ($costs->currentPage() - 1) * $costs->perPage() + $loop->iteration }}</td>
                        <td>{{ $cost->costType->type }}</td>
                        <td class="text-{{ $cost->cost_value > 0 ? 'success' : 'danger' }}">{{ $cost->cost_value }} €
                        </td>
                        <td>{{ $cost->cost_value > 0 ? 'إيداع' : 'سحب' }}</td>
                        <td>{{ $cost->user->name }}</td>
                        <td>{{ $cost->created_at }}</td>
                        <td>{{ $cost->note }}</td>
                        <td>
                            <!-- Delete Form -->
                            <form action="{{ Auth::user()->type == 1 ? route('costs.destroy') : route('costs.entry.destroy') }}" method="POST"
                                onsubmit="return confirmDelete()">
                                <input type="hidden" name="cost" value="{{ $cost->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div id="paginate">
            {{ $costs->appends(request()->query())->links() }}
        </div>


    </div>
    <!-- Insert Modal -->
    <div class="modal fade" id="addNewCost" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">إضافة مصروف</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label for="tran">نوع العملية</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="transactionType" id="withdraw" value="1"
                            checked>
                        <label class="form-check-label" for="withdraw">
                            سحب
                        </label>
                    </div>
                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="transactionType" id="deposit" value="2">
                        <label class="form-check-label" for="deposit">
                            إيداع
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="fullName">نوع المصروف*</label>
                        <select id="costType" class="form-control">
                            <option value="">------اختر النوع-------</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="value">قيمة المصروف*</label>
                        <input type="number" step="0.01" class="form-control" id="value">
                    </div>
                    <div class="form-group">
                        <label for="note">ملاحظة</label>
                        <textarea id="note" cols="30" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-dismiss="modal">{{ __('buttons.close') }}</button>
                    <button type="submit" class="btn btn-primary" id="addCostSubmit">{{ __('buttons.save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#addCostSubmit").click(function() {
                $('#addCostSubmit').prop('disabled', true);
                var value = $("#value").val();
                var type = $('#costType').val();
                var note = $('#note').val();
                var transictionType = $('input[name="transactionType"]:checked').val();
                if (!value || !type) {
                    alert('يجب ملئ الحقول')
                    return;
                } else {
                    @if (auth()->user()->type == 1)
                        $.ajax({
                            url: "{{ route('costs.store') }}",
                            method: 'post',
                            data: {
                                value,
                                type,
                                note,
                                transictionType,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                // Update the product info div with the retrieved data
                                $('#successAlert').removeClass('d-none')
                                $('#successAlert').text(response.message)
                                $("#addNewCost").modal("hide");
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    @else
                        $.ajax({
                            url: "{{ route('costs.entry.store') }}",
                            method: 'post',
                            data: {
                                value,
                                type,
                                note,
                                transictionType,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                // Update the product info div with the retrieved data
                                $('#successAlert').removeClass('d-none')
                                $('#successAlert').text(response.message)
                                $("#addNewCost").modal("hide");
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    @endif

                }
            })



        })

        function confirmDelete() {
            return confirm('هل انت متأكد من حذف هذا العنصر?');
        }
    </script>
@endsection
