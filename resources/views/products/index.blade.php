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
    <div class="row">
        <div class="col-sm-3">
            @if (auth()->user()->type == 1)
                <a href="{{ route('products.create') }}" class="btn btn-success"><i class="fa fa-plus"></i>
                    {{ __('buttons.add') }}</a>
            @endif
        </div>
        @if ($totalWeightOnSelledDate)
            <div class="col-sm-4 text-bold">الوزن المباع في هذا التاريخ : {{ $totalWeightOnSelledDate }} غ</div>
        @endif

    </div>

    <hr>
    <div class="mt-3">
        <form action="{{ route('products.index') }}" method="GET">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <span class="label">الحالة</span>
                        <select name="status" id="" class="form-control">
                            <option value="0"> الجميع</option>
                            <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>مباع</option>
                            <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>متوفر</option>
                            <option value="3" {{ request('status') == 3 ? 'selected' : '' }}>تالف</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <span class="label">النوع</span>

                        <select class="type-select form-control" name="type">
                            <option value="0">الجميع</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}</option>
                            @endforeach
                        </select>


                        {{-- <select name="type" id="" class="form-control">
                            <option value="0">الجميع</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}</option>
                            @endforeach
                        </select> --}}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <span class="label">العيار</span>
                        <select name="caliber" id="caliber" class="form-control">
                            <option value="0">الجميع</option>
                            @foreach ($calibers as $caliber)
                                <option value="{{ $caliber->id }}"
                                    {{ request('caliber') == $caliber->id ? 'selected' : '' }}>{{ $caliber->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <span class="label">المبيعات - من</span>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <span class="label"> الى</span>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div class="col-md-2 mt-4">
                    <button type="submit" class="btn btn-warning mb-2">تنفيذ</button>
                    <a href="{{ route('products.index') }}" class="btn btn-danger mb-2">ضبط</a>
                </div>
            </div>
        </form>
    </div>

    <!--  Search Form -->
    <form id="searchForm" class="form-inline my-2">
        <div class="form-group">
            <input type="text" id="searchValue" class="form-control" placeholder="الوزن - المعرّف - رمز المنتج"
                autocomplete="off">
        </div>
        <button type="submit" id="submitButton" class="btn btn-success">بحث</button>
    </form>

    </div>


    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    {{-- <th>المعرف</th> --}}
                    <th>رمز المنتج</th>
                    {{-- <th>الاسم</th> --}}
                    <th>{{ __('types.type') }}</th>
                    <th>{{ __('caliber.caliber') }}</th>
                    <th>سعر المبيع </th>
                    <th>الوزن</th>
                    <th>سعر الغرام</th>
                    <th> البائع </th>
                    <th>الحالة </th>
                    <th>المصدر</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="searchResult">

                @foreach ($products as $product)
                    <tr class="text-{{ !empty($product->description) ? 'success' : '' }}">
                        <td class=" bg-{{ !empty($product->description) ? 'warning' : '' }}">
                            {{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                        {{-- <td>{{ $product->ident }}</td> --}}
                        <td>{{ $product->short_ident }}</td>
                        {{-- <td>{{ $product->name ?? '-' }}</td> --}}
                        <td>{{ $product->type->name }}</td>
                        <td>{{ $product->caliber->full_name }}</td>
                        <td> {{ $product->selled_price > 0 ? $product->selled_price . ' €' : '-' }}</td>
                        <td>{{ $product->weight }}</td>
                        <td> {{ $product->selled_price > 0 ? number_format($product->selled_price / $product->weight, 2) : '-' }}
                        </td>
                        <td>{{ $product->user->name ?? '-' }}</td>
                        @php
                            $status = ['', 'مباع', 'متوفر', 'تالف', '', '', 'توصاي', 'محجوز'];
                            $statusColor = ['', 'danger', 'success', 'warning', '', '', '', 'orange'];
                        @endphp
                        <td><span
                                class="bg bg-{{ $statusColor[$product->status ] }} p-1">{{ $status[$product->status ] }}</span>
                        </td>
                        <td>{{ $product->quantity->type->name ?? 'جديد' }}</td>
                        <td class="d-flex">
                            <a href="{{ route('products.show', $product->id) }}" target="_blank"
                                class="btn btn-primary text-white btn-sm "><i class="fas fa-eye"></i></a>
                            @if (auth()->user()->type == 1)
                                @if ($product->short_ident)
                                    <a href="{{ route('products.edit', $product->id) }}" target="_blank"
                                        class="btn btn-secondary  text-white btn-sm mx-1"><i class="fas fa-edit"></i></a>
                                @endif
                                <form action="{{ route('products.destroy', $product->id) }}" method="post" id="deleteForm"
                                    onsubmit="return confirmDelete()">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm delete-button " type="submit"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                                {{-- <button class="btn btn-success btn-sm sellProductBtn ml-1" data-toggle="modal"
                                    data-target="#sellProduct" data-short-ident="{{ $product->short_ident }}"><i
                                        class="fas fa-receipt"></i></button> --}}
                                @if ($product->status == 2)
                                    <form action="{{ route('products.sell', $product->id) }}" method="post" id="sellForm"
                                        onsubmit="return confirmSell({{ $product->id }})">
                                        @csrf
                                        @method('post')
                                        <input type="hidden" id="new_price_{{ $product->id }}" name="new_price"
                                            value="">
                                        <button class="btn btn-info btn-sm btn-info delete-button mx-1" type="submit">
                                            بيع
                                        </button>
                                    </form>
                                @endif
                                @if ($product->status == 1 && !is_null($product->short_ident))
                                    <!-- مباع منتج عادي -->
                                    <a class="btn btn-sm btn-warning mx-1"
                                        href="{{ route('products.refund', $product->id) }}" target="_blank"
                                        rel="noopener noreferrer">استرجاع</a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div id="paginate">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Sell Product Form -->
    {{-- <div class="modal fade" id="sellProduct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> بيع منتج</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success d-none" id="TransfareAlertSuccess"></div>
                    <div class="alert alert-danger d-none" id="TransfareAlertError"></div>
                    <form>
                        <div class="form-group" id="weightSection">
                            <label for="totalPrice" class="col-form-label">السعر الإجمالي:</label>
                            <input type="number" step="0.01" class="form-control weight" id="totalPrice"
                                required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">الغاء</button>
                    <button type="button" id="TransfareQuantitySubmit" class="btn btn-primary">حفظ</button>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.type-select').select2();
            // $("#sellProduct").on('shown.bs.modal', function() {
            //     $(this).find('#totalPrice').focus();
            //     // $('#name-error').text('');
            // });

            //   //******************** add quantity open Modal
            //   $(".sellProductBtn").click(function() {
            //     var short_ident = $(this).data("short_ident-quantity");
            //     // var type = $(this).data("type-value");
            //     $("#AddShortIdentValue").val(short_ident_quantity);
            //     console.log(short_ident_quantity)
            //     // $("#typeValue").val(type);
            // });

        });

        function confirmDelete() {
            return confirm('هل انت متأكد من حذف المنتج');
        }

        function confirmSell(product) {
            var userInput;

            // Keep prompting until a non-empty value is entered or the user clicks Cancel
            while (true) {
                userInput = prompt('هل انت متأكد من بيع المنتج');

                // Check if the user clicked Cancel
                if (userInput === null) {
                    console.log('User clicked Cancel');
                    return false;
                }

                // Break the loop if a non-empty value is entered
                if (userInput.trim() !== '') {
                    break;
                }
            }

            // Now userInput contains a non-empty value

            // Use a regular expression to check if the input is a valid floating-point number
            var floatValue = parseFloat(userInput);

            // Check if the parsed value is a valid number
            if (!isNaN(floatValue) && isFinite(floatValue)) {
                // Assign the parsed value to the input field
                document.getElementById('new_price_' + product).value = floatValue;
                // Continue with the form submission
                return true;
            } else {
                // Inform the user about an invalid input
                alert('Please enter a valid number.');
                return false;
            }
        }

        $('#searchForm').submit(function(event) {
            event.preventDefault();
            var searchValue = $('#searchValue').val();
            console.log(searchValue);
            if (searchValue) {
                $('#submitButton').prop('disabled', true)
                $.ajax({
                    url: '/product_search/' + searchValue,
                    method: 'GET',
                    contentType: 'application/json', // Set content type to JSON
                    data: { // Stringify the data object
                        searchValue: searchValue,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Handle response if needed
                        $('#searchResult').html(response.html)
                        $('#submitButton').prop('disabled', false)
                        $('#paginate').html('')
                        $('#searchValue').val('');
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        $('#submitButton').prop('disabled', false)

                        // Handle error if needed
                    }
                });
            } else {

            }
        });
    </script>
@endsection
