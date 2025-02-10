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
                <a href="{{ route('dataentry.create') }}" class="btn btn-success"><i class="fa fa-plus"></i>
                    {{ __('buttons.add') }}</a>
        </div>

    </div>

    <hr>

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
                    <th>الوزن</th>
                    <th>الحالة </th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="searchResult">

                @foreach ($products as $product)
                    <tr class="text-{{ !empty($product->description) ? 'success' : '' }}">
                        <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                        {{-- <td>{{ $product->ident }}</td> --}}
                        <td>{{ $product->short_ident }}</td>
                        {{-- <td>{{ $product->name ?? '-' }}</td> --}}
                        <td>{{ $product->type->name }}</td>
                        <td>{{ $product->caliber->full_name }}</td>
                        <td>{{ $product->weight }}</td>
                        @php
                            $status = ['', 'مباع', 'متوفر', 'تالف', '', '', ''];
                            $statusColor = ['', 'danger', 'success', 'warning', '', '', '', ''];
                        @endphp
                        <td><span
                                class="bg bg-{{  $statusColor[$product->status] }} p-1">{{ $status[$product->status] }}</span>
                        </td>

                        <td><a href="{{ route('dataentry.show', $product->id) }}" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div id="paginate">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

@section('scripts')

<script>
     $('#searchForm').submit(function(event) {
            event.preventDefault();
            var searchValue = $('#searchValue').val();
            console.log(searchValue);
            if (searchValue) {
                $('#submitButton').prop('disabled', true)

                $.ajax({
                    url: '/product_search/entry/' + searchValue,
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