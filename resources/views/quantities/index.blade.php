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
    @if (auth()->user()->type == 1)
        <a href="{{ route('quantities.create') }}" target="_blank" class="btn btn-success"> <i class="fas fa-plus"></i> كميّة
            جديدة</a>
    @endif

    <hr>
    <div class="alert alert-success d-none" id="alertSuccess"></div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رمز الكمية</th>
                    <th> العيار</th>
                    <th>الصنف</th>
                    <th>الوزن الكامل</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quantities as $quantity)
                    <tr>
                        <td>{{ ($quantities->currentPage() - 1) * $quantities->perPage() + $loop->iteration }}</td>
                        <td>{{ $quantity->short_ident }}</td>
                        <td>{{ $quantity->caliber->full_name }}</td>
                        <td>{{ $quantity->type->name }}</td>
                        <td><b id="sum-{{ $quantity->short_ident }}">{{ $quantity->weight_quantities_sum_weight }}</b> g</td>
                        <td class="d-flex">
                            @if (auth()->user()->type == 1)
                                <button class="btn btn-success btn-sm AddQuantityBtn ml-1" id="AddQuantity"
                                    data-toggle="modal" data-target="#AddQuantityModal"
                                    data-short_ident-quantity="{{ $quantity->short_ident }}
                        "
                                    data-type-value="{{ $quantity->type->id }}"><i class="fas fa-plus"></i></button>
                                <button class="btn btn-danger btn-sm editQuantityBtn ml-1" data-target="#EditQuantityModal"
                                    data-toggle="modal" data-short-ident="{{ $quantity->short_ident }}"
                                    data-title = " تعديل الصنف {{ $quantity->type->name }} "><i
                                        class="fas fa-minus"></i></button>
                                <button class="btn btn-secondary btn-sm transfareQuantityBtn ml-1" data-toggle="modal"
                                    data-target="#TransfareQuantityModal"
                                    data-short-ident="{{ $quantity->short_ident }}"><i
                                        class="fas fa-exchange-alt"></i></button>
                            @endif
                            <a href="{{ route('quantities.quantity_details', $quantity->id) }}" target="_blank"
                                class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div id="paginate">
        {{ $quantities->appends(request()->query())->links() }}
    </div>


    <!-- Add Quantity Modal -->
    <div class="modal fade" id="AddQuantityModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">زيادة وزن الصنف</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success d-none" id="AddAlertSuccess"></div>
                    <form>
                        <div class="form-group">
                            <select id="AddModalStatus" class="form-control">
                                <option value="4">وزن خارجي</option>
                                <option value="5">تقصير منتج</option>
                                <option value="6">إتلاف</option>
                                <option value="9">شراء ذهب</option>
                            </select>
                        </div>
                        <div class="form-group" id="weightSection">
                            <label for="weight" class="col-form-label">الوزن بالغرام:</label>
                            <input type="number" step="0.01" class="form-control weight" id="weight">
                            <input type="hidden" id="AddShortIdentValue">
                            <span class="text text-danger" id="AddWeightRequiredAlert"></span>
                        </div>

                        <div class="form-group buyqunatity d-none">
                            <label for="quantityPrice">السعر الإجمالي:</label>
                            <input type="number" step="0.01" class="form-control" id="quantityPrice">
                            <span class="text text-danger" id="AddPriceRequiredAlert"></span>
                        </div>

                        <div class="form-group ounce_price d-none">
                            <label for="quantityPrice"> سعر التحويل:</label>
                            <input type="number" step="0.01" class="form-control" id="ounce_price">
                            <span class="text text-danger" id="AddOuncePriceAlert"></span>
                        </div>

                        <div class="form-group d-none" id="shortIdentForProduct">
                            <label for="AddproductShortIdent">رمز المنتج:</label>
                            <input type="number" class="form-control" id="AddproductShortIdent">
                            <span class="text text-danger" id="shortIdentForProductAlert"></span>
                        </div>

                        <div id="AddProductInfoSection" class="border border-success p-2 d-none">
                            <div class="row">
                                <div class="col-sm-2">
                                    <span>الرمز :</span><span id="AddProductShortId"></span>
                                </div>
                                <div class="col-sm-3">
                                    <span> الوزن:</span><span id="AddProductWeight"></span>
                                </div>
                                <div class="col-sm-4">
                                    <span> الصنف:</span><span id="AddProductType"></span>
                                </div>
                                <div class="col-sm-3">
                                    <span> العيار:</span><span id="AddProductCaliber"></span>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="form-group d-none" id="AddSelledPriceSection">
                            <label for="AddSelledPrice">سعر المبيع:</label>
                            <input type="number" id="AddSelledPrice" class="form-control">
                            <span class="text text-danger" id="AddPriceRequiredAlert"></span>
                        </div> --}}

                        <div class="form-group">
                            <label for="notice">ملاحظة:</label>
                            <textarea class="form-control" id="notice" cols="30" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">الغاء</button>
                    <button type="button" id="AddQuantitySubmit" class="btn btn-primary">حفظ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Quantity Modal -->
    <div class="modal fade" id="EditQuantityModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success d-none" id="EditSuccessMessage"></div>
                    <form>
                        <div class="form-group">
                            <select id="status" class="form-control">
                                <option value="1">مباع مفرد</option>
                                <option value="2">تطويل</option>
                                <option value="3">تدوير</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editWeight" class="col-form-label">الوزن بالغرام:</label>
                            <input type="number" step="0.01" class="form-control weight" id="editWeight">
                            <input type="hidden" id="EditshortIdentValue">
                            <span class="text text-danger" id="editWeightRequiredAlert"></span>

                        </div>

                        <div class="form-group d-none" id="productShortIdentSection">
                            <label for="productShortIdent">رمز المنتج:</label>
                            <input type="number" class="form-control" id="productShortIdent">
                        </div>

                        <div id="productInfoSection" class="border border-success p-2 d-none">
                            <div class="row">
                                <div class="col-sm-2">
                                    <span>الرمز:</span><span id="productShortId"></span>
                                </div>
                                <div class="col-sm-3">
                                    <span> الوزن:</span><span id="productWeight"></span>
                                </div>
                                <div class="col-sm-4">
                                    <span> الصنف:</span><span id="productType"></span>
                                </div>
                                <div class="col-sm-3">
                                    <span> العيار:</span><span id="productCaliber"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" id="selledPriceSection">
                            <label for="EditselledPrice">سعر المبيع:</label>
                            <input type="number" id="editselledPrice" class="form-control">
                            <span class="text text-danger" id="priceRequiredAlert"></span>
                        </div>
                        <div class="form-group">
                            <label for="editNotice">ملاحظة:</label>
                            <textarea class="form-control" id="editNotice" cols="30" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">الغاء</button>
                    <button type="button" id="EditQuantitySubmit" class="btn btn-primary">حفظ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transare Quantity Modal -->
    <div class="modal fade" id="TransfareQuantityModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">تحويل من كمية الى كمية</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success d-none" id="TransfareAlertSuccess"></div>
                    <div class="alert alert-danger d-none" id="TransfareAlertError"></div>
                    <form>
                        <div class="form-group">
                            <label for="from">إلى الصنف</label>
                            <select id="toquantity" class="form-control">
                                @foreach ($allQuantities as $quantity)
                                    <option value="{{ $quantity->short_ident }}">{{ $quantity->type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="weightSection">
                            <label for="weight" class="col-form-label">الوزن بالغرام:</label>
                            <input type="number" step="0.01" class="form-control weight" id="TransfareWeight"
                                required>
                            <b class="text-danger d-none" id="weightTrasnfareRequired">الوزن مطلوب</b>
                            <input type="hidden" id="TransfareShortIdentValue">
                            <span class="text text-danger" id="AddWeightRequiredAlert"></span>
                        </div>

                        <div class="form-group">
                            <label for="notice">ملاحظة:</label>
                            <textarea class="form-control" id="TransfareNotice" cols="30" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">الغاء</button>
                    <button type="button" id="TransfareQuantitySubmit" class="btn btn-primary">حفظ</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            //set autofocus to type name field
            $("#AddQuantityModal").on('shown.bs.modal', function() {
                $(this).find('#weight').focus();
                // $('#name-error').text('');
            });

            // ************add modal
            $("#AddQuantityModal").on('hidden.bs.modal', function() {
                $("#weight").val('');
                $("#typeValue").val('');
                $("#notice").val('');
                $("#AddproductShortIdent").val('');
                $("#quantityPrice").val('');
                $("#AddProductInfoSection").addClass('d-none');
                $("#shortIdentForProductAlert").addClass('d-none');
            });
            //******************** add quantity open Modal
            $(".AddQuantityBtn").click(function() {
                var short_ident_quantity = $(this).data("short_ident-quantity");
                // var type = $(this).data("type-value");
                $("#AddShortIdentValue").val(short_ident_quantity);
                console.log(short_ident_quantity)
                // $("#typeValue").val(type);
            });

            // change inputs when ever user change the status in add quantity modal
            $('#AddModalStatus').change(function() {
                var selectedOption = $(this).val();
                if (selectedOption == '4') {
                    $('#shortIdentForProduct').addClass('d-none');
                    $('#AddProductInfoSection').addClass('d-none');
                    $('#AddSelledPriceSection').addClass('d-none');
                    $('#weightSection').removeClass('d-none');
                    $('.buyqunatity').addClass('d-none');
                    $('.ounce_price').addClass('d-none');

                } else if (selectedOption == '5') {
                    $('#shortIdentForProduct').removeClass('d-none');
                    $('#AddSelledPriceSection').addClass('d-none');
                    $('.buyqunatity').addClass('d-none');
                    $('.ounce_price').addClass('d-none');
                } else if (selectedOption == '6') {
                    $('#shortIdentForProduct').removeClass('d-none');
                    $('#AddSelledPriceSection').addClass('d-none');
                    $('#weightSection').addClass('d-none');
                    $('.buyqunatity').addClass('d-none');
                    $('.ounce_price').addClass('d-none');

                } else if (selectedOption == '9') {
                    $('#weightSection').removeClass('d-none');
                    $('.ounce_price').removeClass('d-none');
                    $('.buyqunatity').removeClass('d-none');
                    $('#shortIdentForProduct').addClass('d-none');

                }
            });

            // retrive product information
            $('#AddproductShortIdent').on('input', function() {
                $('#AddProductInfoSection').addClass('d-none')

                var product = $(this).val();
                // Check if the input value is not empty
                if (product.trim() !== '') {
                    // Make AJAX request to retrieve product info
                    $.ajax({
                        url: '/quantities/get_product_info/' + product, // Replace with your route
                        method: 'GET',
                        data: {
                            product,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            // Update the product info div with the retrieved data
                            if (response.product !== null) {
                                $('#AddProductInfoSection').removeClass('d-none')
                                $('#AddProductShortId').text(response.product.short_ident)
                                $('#AddProductWeight').text(response.product.weight)
                                $('#AddProductType').text(response.product.type.name)
                                $('#AddProductCaliber').text(response.product.caliber.full_name)
                            } else
                                $('#AddProductInfoSection').addClass('d-none')
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                } else {
                    // Clear the product info div if the input is empty
                    $('#productInfo').html('');
                }
            });

            //Add quantity submit 4,5,6
            $("#AddQuantitySubmit").click(function() {
                var status = $("#AddModalStatus").val();
                var short_ident = $("#AddproductShortIdent").val(); // product short_ident
                var quantity_short_ident = $("#AddShortIdentValue").val();
                var weight = $("#weight").val();
                var quantityPrice = $("#quantityPrice").val();
                // var type = $("#typeValue").val();
                var notice = $("#notice").val();
                var ounce_price = $("#ounce_price").val();
                // Send an AJAX request to update the record
                //new quantity from outside

                $('#AddPriceRequiredAlert').text('');
                $('#AddWeightRequiredAlert').text('');
                if ((status == 4 || status == 5 || status == 9) && !weight.trim()) {
                    $('#AddWeightRequiredAlert').text('يرجى ادخال الوزن');
                    return;
                }
                // else if ((status == 5 ) && !selled_price.trim()) {
                //     $('#AddPriceRequiredAlert').text('يرجى ادخال سعر المبيع');
                //     return;
                // }
                else if ((status == 5 || status == 6) && !short_ident.trim()) {
                    $('#shortIdentForProductAlert').text('يرجى ادخال رمز المنتج');
                    return;
                } else if (status == 9 && !quantityPrice.trim()) {
                    $('#AddPriceRequiredAlert').text('يرجى ادخال سعر الشراء الاجمالي للوزن المدخل');
                    return;
                }else if (status == 9 && !ounce_price.trim()) {
                    $('#AddOuncePriceAlert').text('يرجى ادخال  قيمة التحويل لشراء');
                    return;
                } else {
                    $('#AddPriceRequiredAlert').text('');
                    $('#AddWeightRequiredAlert').text('');
                    $('#shortIdentForProductAlert').text('');
                    $(this).prop("disabled", true);
                }
                if (status == 4) {
                    $.ajax({
                        url: "/quantities/add_to_existing_quantity",
                        method: "POST", // Adjust the HTTP method to match your update route
                        data: {
                            status,
                            quantity_short_ident,
                            weight,
                            notice,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            // Handle the success response
                            // Close the modal or update the record on the page
                            $("#AddQuantityModal").modal("hide");
                            $('#alertSuccess').text(response.message);
                            $('#alertSuccess').removeClass('d-none');
                            $('#sum-' + quantity_short_ident).text(response.weight);
                            $("#weight").val('')

                            $("#AddQuantitySubmit").prop("disabled", false);
                            console.log(response.message);
                            // location.reload();

                        },
                        error: function(xhr, status, error) {
                            // Handle the error, if any
                            $('#value-error').text(xhr.responseJSON.message);
                            $("#AddQuantitySubmit").prop("disabled", false);
                            console.log(xhr.responseJSON.message);
                        }
                    });
                }
                // تقصير قطعة
                if (status == 5) {
                    $.ajax({
                        url: "/quantities/add_to_existing_quantity",
                        method: "POST", // Adjust the HTTP method to match your update route
                        data: {
                            status,
                            short_ident, //product short_ident
                            quantity_short_ident,
                            weight,
                            notice,
                            // selled_price,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            // Handle the success response
                            // Close the modal or update the record on the page
                            $("#AddQuantityModal").modal("hide");
                            $('#alertSuccess').text(response.message);
                            $('#alertSuccess').removeClass('d-none');
                            $('#sum-' + quantity_short_ident).text(response.weight);
                            $("#weight").val('')

                            $("#AddQuantitySubmit").prop("disabled", false);
                            console.log(response.message);
                            // location.reload();

                        },
                        error: function(xhr, status, error) {
                            // Handle the error, if any
                            $('#AddAlertSuccess').removeClass('alert-success').addClass(
                                'alert-danger').removeClass('d-none').text(xhr.responseJSON
                                .message);
                            $("#AddQuantitySubmit").prop("disabled", false);
                            console.log(xhr.responseJSON.message);
                        }
                    });
                }

                //اتلاف قطعة
                if (status == 6) {
                    $.ajax({
                        url: "/quantities/add_to_existing_quantity",
                        method: "POST", // Adjust the HTTP method to match your update route
                        data: {
                            status,
                            short_ident, //product short_ident
                            quantity_short_ident,
                            notice,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            // Handle the success response
                            // Close the modal or update the record on the page
                            $("#AddQuantityModal").modal("hide");
                            $('#alertSuccess').text(response.message);
                            $('#alertSuccess').removeClass('d-none');
                            $('#sum-' + quantity_short_ident).text(response.weight);
                            $("#weight").val('')

                            $("#AddQuantitySubmit").prop("disabled", false);
                            console.log(response.message);
                            // location.reload();

                        },
                        error: function(xhr, status, error) {
                            // Handle the error, if any
                            $('#AddAlertSuccess').removeClass('alert-success').addClass(
                                'alert-danger').removeClass('d-none').text(xhr.responseJSON
                                .message);
                            $("#AddQuantitySubmit").prop("disabled", false);
                            console.log(xhr.responseJSON.message);
                        }
                    });
                }
                // شراء ذهب
                if (status == 9) {
                    $.ajax({
                        url: "/quantities/add_to_existing_quantity",
                        method: "POST", // Adjust the HTTP method to match your update route
                        data: {
                            status,
                            quantity_short_ident,
                            weight,
                            notice,
                            ounce_price,
                            price: quantityPrice,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            // Handle the success response
                            // Close the modal or update the record on the page
                            $("#AddQuantityModal").modal("hide");
                            $('#alertSuccess').text(response.message);
                            $('#alertSuccess').removeClass('d-none');
                            $('#sum-' + quantity_short_ident).text(response.weight);
                            $("#weight").val('')
                            $('#AddPriceRequiredAlert').addClass('d-none');
                            $("#AddQuantitySubmit").prop("disabled", false);
                            console.log(response.message);
                            // location.reload();

                        },
                        error: function(xhr, status, error) {
                            // Handle the error, if any
                            $('#value-error').text(xhr.responseJSON.message);
                            $("#AddQuantitySubmit").prop("disabled", false);
                            console.log(xhr.responseJSON.message);
                        }
                    });
                }
            });

            //************* Edit Modal
            //set autofocus to type name field
            $("#EditQuantityModal").on('shown.bs.modal', function(event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
                var title = button.data('title')
                var modal = $(this)
                modal.find('.modal-title').text(title)
                $(this).find('#editWeight').focus();
                // $('#name-error').text('');
            });

            // reset EditQuantityModal when is closed
            $("#EditQuantityModal").on('hidden.bs.modal', function() {
                $("#editWeight").val('');
                $("#editselledPrice").val('');
                $("#editNotice").val('');
                $("#productShortIdent").val('');
                $("#status").val('1');
                $('#productShortIdentSection').addClass('d-none');
                $('#selledPriceSection').show();
                $('#productInfoSection').addClass('d-none')
                // $('#EditSuccessMessage').addClass('d-none');

            });

            //******************** Edit quantity open Modal
            $(".editQuantityBtn").click(function() {
                var short_ident = $(this).data("short-ident");
                $("#EditshortIdentValue").val(short_ident);
                console.log(short_ident);
            });
            // hide selled price input when user select recycle option
            $('#status').change(function() {
                var selectedOption = $(this).val();
                if (selectedOption == '1') {
                    $('#selledPriceSection').show();
                    $('#productShortIdentSection').addClass('d-none');
                    $('#productInfoSection').addClass('d-none')


                } else if (selectedOption == '2') {
                    $('#selledPriceSection').hide();
                    $('#productShortIdentSection').removeClass('d-none');

                } else {
                    $('#selledPriceSection').hide();
                    $('#productShortIdentSection').addClass('d-none');
                    $('#productInfoSection').addClass('d-none')

                }
            });

            // retrive product information
            $('#productShortIdent').on('input', function() {
                var product = $(this).val();

                // Check if the input value is not empty
                if (product.trim() !== '') {
                    // Make AJAX request to retrieve product info
                    $.ajax({
                        url: '/quantities/get_product_info/' + product, // Replace with your route
                        method: 'GET',
                        data: {
                            product,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            // Update the product info div with the retrieved data
                            if (response.product !== null) {
                                $('#productInfoSection').removeClass('d-none')
                                $('#productShortId').text(response.product.short_ident)
                                $('#productWeight').text(response.product.weight)
                                $('#productType').text(response.product.type.name)
                                $('#productCaliber').text(response.product.caliber.full_name)
                            } else
                                $('#productInfoSection').addClass('d-none')
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                } else {
                    // Clear the product info div if the input is empty
                    $('#productInfo').html('');
                }
            });

            // sell Quantity EditQuantityModal status => 1, 2, 3
            $("#EditQuantitySubmit").click(function() {
                var status = $("#status").val();
                var short_ident = $("#EditshortIdentValue").val();
                var productShortIdent = $("#productShortIdent").val();
                var editWeight = $("#editWeight").val();
                var editselledPrice = $("#editselledPrice").val();
                var editNotice = $("#editNotice").val();

                $('#priceRequiredAlert').text('');
                $('#editWeightRequiredAlert').text('');
                if ((status == 1 || status == 2 || status == 3) && !editWeight.trim()) {
                    $('#editWeightRequiredAlert').text('يرجى ادخال الوزن');
                    return;
                } else if ((status == 1) && !editselledPrice.trim()) {
                    $('#priceRequiredAlert').text('يرجى ادخال سعر المبيع');
                    return;
                } else {
                    $('#priceRequiredAlert').text('');
                    $('#editWeightRequiredAlert').text('');
                    $(this).prop("disabled", true);
                }
                // if status is 1 
                if (status == 1) {
                    $.ajax({
                        url: "{{ route('quantities.sell_quantity') }}",
                        method: "POST",
                        data: {
                            short_ident,
                            status,
                            editWeight,
                            editselledPrice,
                            editNotice,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            console.log(response.message)
                            $('#alertSuccess').text(response.message);
                            $('#alertSuccess').removeClass('d-none');
                            $('#sum-' + short_ident).text(response.oldQuality - editWeight);
                            // Handle the response from the server
                            // Close the modal
                            // $("#addNewCaliberForm").modal("hide");
                            $('#EditQuantitySubmit').prop("disabled", false);
                            $("#EditQuantityModal").modal("hide");

                            // location.reload();
                            resetFields();
                        },
                        error: function(xhr, status, error) {
                            // Handle the error, if any
                            // $('#name-error').text(xhr.responseJSON.message);
                            $('#EditQuantitySubmit').prop("disabled", false);
                        }
                    });
                }

                // if status is 2
                if (status == 2) {
                    var oldQuantityWeight = $('#sum-' + short_ident).text();
                    if (!productShortIdent.trim()) {
                        alert('يرجى ادخال رقم المنتج المراد اضافة الوزن اليه!');
                        $(this).prop("disabled", false);

                        return;
                    } else {
                        $(this).prop("disabled", true);
                    }

                    $.ajax({
                        url: "{{ route('quantities.sell_quantity') }}",
                        method: "POST",
                        data: {
                            short_ident, // quantity short_ident
                            status,
                            editWeight,
                            editselledPrice,
                            editNotice,
                            productShortIdent, // product that we want to ass weight to it
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#EditSuccessMessage').removeClass('alert-danger').text(response
                                .message);
                            $('#EditSuccessMessage').removeClass('d-none');
                            // Handle the response from the server
                            // Close the modal
                            // $("#addNewCaliberForm").modal("hide");
                            $('#EditQuantitySubmit').prop("disabled", false);
                            // location.reload();
                            $('#sum-' + short_ident).text(oldQuantityWeight - editWeight);
                            $("#EditQuantityModal").modal("hide");

                            resetFields();

                        },
                        error: function(xhr, status, error) {
                            // Handle the error, if any
                            // $('#name-error').text(xhr.responseJSON.message);
                            $('#EditSuccessMessage').removeClass('d-none');
                            $('#EditSuccessMessage').addClass('alert-danger').text(xhr
                                .responseJSON.message);
                            $('#EditQuantitySubmit').prop("disabled", false);
                        }
                    });
                }

                // if status is 3
                if (status == 3) {
                    var oldQuantityWeight = $('#sum-' + short_ident).text();

                    $.ajax({
                        url: "{{ route('quantities.sell_quantity') }}",
                        method: "POST",
                        data: {
                            short_ident, // quantity short_ident
                            status,
                            editWeight,
                            editNotice,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#EditSuccessMessage').removeClass('alert-danger').text(response
                                .message);
                            $('#EditSuccessMessage').removeClass('d-none');
                            // Handle the response from the server
                            // Close the modal
                            // $("#addNewCaliberForm").modal("hide");
                            $('#EditQuantitySubmit').prop("disabled", false);
                            // location.reload();
                            $('#sum-' + short_ident).text(oldQuantityWeight - editWeight);
                            $("#EditQuantityModal").modal("hide");

                            resetFields();

                        },
                        error: function(xhr, status, error) {
                            // Handle the error, if any
                            // $('#name-error').text(xhr.responseJSON.message);
                            $('#EditSuccessMessage').removeClass('d-none');
                            $('#EditSuccessMessage').addClass('alert-danger').text(xhr
                                .responseJSON.message);
                            $('#EditQuantitySubmit').prop("disabled", false);
                        }
                    });
                }

            });

            //******************** Edit transafre quantity open Modal
            $(".transfareQuantityBtn").click(function() {
                var short_ident = $(this).data("short-ident");
                $("#TransfareShortIdentValue").val(short_ident);
                console.log(short_ident);
            });

            // sell Quantity EditQuantityModal status => 1, 2, 3
            $("#TransfareQuantitySubmit").click(function() {
                var short_ident = $("#TransfareShortIdentValue").val();
                var TransfareWeight = $("#TransfareWeight").val();
                var TransfareNotice = $("#TransfareNotice").val();
                var toquantity = $("#toquantity").val();
                $("#TransfareQuantitySubmit").prop("disabled", true);
                if (!TransfareWeight) {
                    $('#weightTrasnfareRequired').removeClass('d-none')
                    return;
                }
                $.ajax({
                    url: "{{ route('quantities.transfareQuantity') }}",
                    method: "POST",
                    data: {
                        short_ident, // quantity short_ident
                        TransfareWeight,
                        TransfareNotice,
                        toquantity,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $("#TransfareQuantityModal").modal("hide");
                        $('#TransfareQuantitySubmit').removeClass('disabled');

                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        $("#TransfareQuantitySubmit").prop("disabled", false);
                        $('#TransfareAlertError').removeClass('d-none');
                        $('#TransfareAlertError').text(xhr
                            .responseJSON.message)
                    }
                });


            });

        });

        function resetFields() {
            $("#editWeight").val('');
            $("#editselledPrice").val('');
            $("#editNotice").val('');
            $("#productShortIdent").val('');
            $("#status").val('1');
            $('#productShortIdentSection').addClass('d-none');
            $('#selledPriceSection').show();
            $('#productInfoSection').addClass('d-none')
            $('#AddSelledPriceSection').addClass('d-none');

        }
    </script>
@endsection
