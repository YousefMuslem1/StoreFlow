@extends('employer.layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">بيع جملة</h1>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-8 d-flex">
            <input type="text" name="ident" id="ident" class="form-control" autofocus autocomplete="off">
            <button id="addProduct" class="btn btn-success"><i class="fas fa-plus"></i></button>

        </div>

    </div>
    <b class="text-danger d-none mt-1" id="errorAlert">هذا المنتج مباع أو غير متوفر</b>
    {{-- <button type="submit" id="searchButton" class="btn btn-success mt-3">بحث</button> --}}
    {{-- <a href="{{ route('sell.index') }}" class="btn btn-secondary mt-3">تحديث</a> --}}
    <div class="alert alert-success d-none" id="successAlert"> </div>

    <div>
        <table class="table table-bordered mt-3">
            <thead>
                <th>رقم المنتج</th>
                <th>الوزن</th>
                <th>النوع</th>
            </thead>
            <tbody id="product-container">
                {{-- <tr>
                <td>response.short_ident</td>
                <td>response.weight</td>
                <td>response.type.name</td>
            </tr> --}}
            </tbody>
        </table>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <tbody>
            <tr>
                <th>عدد المنتجات</th>
                <td id="total-products"></td>
            </tr>
            <tr>
                <th>الوزن الكلي</th>
                <td id="total-weight"> </td>
            </tr>
        </tbody>
    </table>
    <div class="form-group">
        <label for="price">السعر الإجمالي</label>
        <input type="number" step="0.01" class="form-control" id="price">
    </div>
    <button type="button" id="sellProducts" class="btn btn-success mt-2">تنفيذ البيع</button>
@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            var fetchedIdentifiers = [];
            var totalWeight = 0;

            function fetchProduct() {
                $('#errorAlert').addClass('d-none');

                var identifier = $('#ident').val();
                if (!identifier) {
                    return;
                }
                $.ajax({
                    method: 'get',
                    url: '/sell/show/',
                    data: {
                        identifier: identifier
                    },
                    success: function(response) {
                        // Display fetched product information
                        if (response) {
                            if (fetchedIdentifiers.includes(response.ident)) {
                                alert('هذا العنصر موجود بالفعل');
                                return; // Do not proceed if identifier is already fetched
                            }
                            var productHTML = '<tr>' + '<td>' + response.short_ident +
                                '</td>' + '<td>' + response.weight + '</td>' + '<td>' +
                                response.type.name + '</td>' + '</tr>';
                            fetchedIdentifiers.push(response.ident);
                            totalWeight += parseFloat(response.weight);
                            if (!isNaN(totalWeight)) {
                                // Convert the total weight to a string with two decimal places
                                totalWeight = parseFloat(totalWeight.toFixed(2));
                            }
                            // totalWeight = totalWeight.toFixed(2);
                            console.log(fetchedIdentifiers);
                            // Append the product information to the DOM
                            $('#product-container').append(productHTML);
                            $('#ident').val('')
                            countTotalProducts()
                        } else {
                            // Product not found
                            // $('#product-info').html('<p>Product not found!</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle errors
                        $('#errorAlert').removeClass('d-none')

                        console.error(error);
                    }
                });
            }
            // Event listener for input field
            $('#ident').on('keyup', function() {
                if (event.keyCode === 13) {
                    fetchProduct();
                }
            });

            // Handle button click
            $('#addProduct').on('click', function() {
                fetchProduct();
            });

            function countTotalProducts() {
                $('#total-products').text(fetchedIdentifiers.length);
                $('#total-weight').text(totalWeight + ' g');
            }

            $('#sellProducts').on('click', function() {
                var totalPrice = $('#price').val();
                if (fetchedIdentifiers.length == 0) {
                    alert('يجب إدخال منتج على الأقل');
                    return; // Cancel the request
                }
                if (!totalPrice.trim()) {
                    alert('سعر المبيع الإجمالي مطلوب');
                    return; // Cancel the request
                }
                if (totalPrice.trim() < 5) {
                    alert('سعر المبيع غير صحيح ');
                    return; // Cancel the request
                }
                $.ajax({
                    method: 'POST',
                    url: "{{ route('sell.many.products') }}",
                    data: {
                        identifiers: fetchedIdentifiers,
                        totalWeight,
                        totalPrice,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#successAlert').removeClass('d-none')
                        $('#successAlert').text(response)

                        console.log(response);

                    },
                    error: function(xhr, status, error) {
                        // Handle errors
                    }
                });
            });
        });
    </script>
@endsection
