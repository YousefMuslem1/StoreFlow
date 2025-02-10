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
    <div class="container">

        <div class="row d-flex justify-center align-items-center">
            <div class="col-md-3">
                <div class="form-group mb-2">
                    <label for="type">النوع</label>
                    <select name="type" id="type" class="form-control">
                        <option value="0"> الجمبع</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group mb-2 mr-2">
                    <label for="caliber">العيار</label>
                    <select name="caliber" id="caliber" class="form-control">
                        <option value="0">الجمبع </option>
                        @foreach ($calibers as $caliber)
                            <option value="{{ $caliber->id }}">{{ $caliber->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-12 col-md-6">

                <div class="table-container">
                    <table class="table table-striped">
                        <thead>
                        </thead>
                        <tbody>
                            <tr>
                                <td> عدد المنتجات في الجرد</td>
                                <td id="readItems">0</td>
                            </tr>
                            <tr>
                                <td>النوع</td>
                                <td id="invantoryType">الجميع</td>
                            </tr>
                            <tr>
                                <td>العيار</td>
                                <td id="invantoryCaliber">الجميع</td>
                            </tr>
                            <tr>
                                <td>عدد المنتجات الكلي في المخزن</td>
                                <td id="allItemsCount">0</td>
                            </tr>
                            <tr>
                                <td>الوزن الكلي في المخزن</td>
                                <td id="allItemsSum">0</td>
                            </tr>
                            <tr>
                                <td>الوزن الناقص في الجرد</td>
                                <td id="notAvSum">0</td>
                            </tr>
                            <tr>
                                <td>الوزن المقروء</td>
                                <td id="sumResult">0</td>
                            </tr>
                            <tr>
                                <td>عدد القطع الناقصة</td>
                                <td id="countAvSum">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- <div class="col-sm-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkAllProducts">
                    {{-- <label class="form-check-label" for="checkAllProducts">
                        جرد كامل
                    </label> 
                </div>
            </div> --}}
            <!-- Display loading button -->
        </div>

        {{-- <h3 class="text-danger d-none notic">الرجاء ترك النافذة مفتوحة حتى الانتهاء من عمليه الجرد وتوقف علامة الانتظار!
        </h3> --}}
        <h5 class="text-success d-none notic">البرنامج جاهز لعملية الجرد!</h5>
        <h2 id="inv-calc"></h2>
        <form class="rfidform" action="{{ route('invantory.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- col-sm-12 col-md-6  -->
                <div class="col-sm-12 col-md-6">
                    <textarea class="form-control textarearfid" name="" id="textArea" rows="3" cols="5" autofocus
                        dir="ltr"></textarea>
                    <div class="d-flex justify-content-center">
                        <div class="lds-roller d-none" id="loader">
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                    <input type="file" class="form-control mt-2" id="excelFile">
                    {{-- <button id="startInvantory" class="btn btn-success mt-2 form-control"> بدء عملية الجرد</button> --}}
                </div>
        </form>


    </div>

    <div class="row">
        <div class="">
            <label for=""></label>
            <button id="calcButton" class="btn btn-primary  mt-2 form-control">معالجة</button> <!-- d-none -->
        </div>

    </div>
    <div class="alert alert-success d-none" id="invantorySaveMessage">تم حفظ الجرد بنجاح!</div>

    <div class="container" id="showResult"></div>
    {{-- <button class="btn btn-success d-none" id="saveInvantoryBtn">حفظ العملية</button> --}}
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var typingTimer;
            var startInvantoryClicked = false;
            var doneTypingInterval = 1000; // 1 second
            var itemsArray = [];
            var allResponseItems;

            // $('#textArea').keydown(function(event) {
            //     if (event.keyCode === 13) { // Check if Enter key was pressed
            //         var textareaValue = $(this).val(); // Get the value of the textarea
            //         var lines = textareaValue.split('\n'); // Split the textarea value by newline characters
            //         lines.forEach(function(line) { // Iterate over each line
            //             var trimmedLine = line.trim(); // Remove leading and trailing whitespace
            //             if (trimmedLine !== '') { // Check if the line is not empty
            //                 itemsArray.add(trimmedLine); // Push the trimmed line to the array
            //             }
            //         });
            //         console.log([...itemsArray]); // Log the array (you can remove this line)
            //     }
            // });

            function calculateUniqueItems() {
                var textareaContent = $('#textArea').val();
                var lines = textareaContent.split('\n');

                lines.forEach(function(line) {
                    var trimmedLine = line.trim().toUpperCase(); 

                    if (trimmedLine !== "") {
                        if (itemsArray.indexOf(trimmedLine) === -1) {
                            itemsArray.push(trimmedLine);
                        }
                    }
                    console.log(itemsArray)
                });
                // Display unique items count
                $('#readItems').text(itemsArray.length);

                // console.log(itemsArray);
            }

            // Bind the function to the keypress event
            $('#textArea').on('keypress', function(e) {
                if (e.which === 13) { // 13 is the key code for Enter
                    calculateUniqueItems();
                }
            });
            
            // retrieve the result of invantory from DB
            $('#calcButton').on('click', function() {
                //  itemsArray = Array.from(itemsArray);
                $("#loader").addClass('d-block');
                $(this).prop('disabled', true);
                // var isChecked = $('#checkAllProducts').prop('checked');
                var formData = new FormData();
                // Append the file input element's files to the FormData object
                formData.append('excelFile', $('#excelFile')[0].files[0]);

                // Append other form data to the FormData object
                formData.append('type', $('#type').val());
                formData.append('caliber', $('#caliber').val());
                formData.append('addtionItems[]', itemsArray);
                formData.append('_token', '{{ csrf_token() }}');
                // formData.append('checkAllProducts', isChecked); // Add if needed
                $.ajax({
                    url: "/result",
                    method: "POST",
                    data: formData, // Pass the FormData object directly here
                    processData: false, // Prevent jQuery from processing the data
                    contentType: false, // Prevent jQuery from setting the Content-Type header
                    cache: false, // Disable caching
                    // checkAllProducts: isChecked,
                    // processData: false,
                    // contentType: false,
                    success: function(response) {
                        $("#loader").removeClass('d-block').addClass('d-none');
                        $("#saveInvantoryBtn").removeClass('d-none').addClass('d-block');

                        $("#showResult").html(response.html);
                        $('#readItems').text(response.readItems);
                        $('#allItemsCount').text(response.count);
                        $('#allItemsSum').text(response.sum);
                        $('#notAvSum').text(response.notAvSum);
                        $('#countAvSum').text(response.countAvSum);
                        let allItemsSumText = $('#allItemsSum').text().trim().replace(
                            /[^0-9.-]+/g, "");
                        let notAvSumText = $('#notAvSum').text().trim().replace(/[^0-9.-]+/g,
                            "");

                        console.log('allItemsSumText:', allItemsSumText);
                        console.log('notAvSumText:', notAvSumText);

                        // Convert the cleaned text to numbers
                        let allItemsSum = parseFloat(allItemsSumText);
                        let notAvSum = parseFloat(notAvSumText);

                        // Check if the conversion was successful
                        if (isNaN(allItemsSum) || isNaN(notAvSum)) {
                            console.error('Error: One of the values is not a number');
                            $('#sumResult').text('Error');
                            return;
                        }

                        // Calculate the sum
                        let sum = allItemsSum - notAvSum;

                        $('#sumResult').text(sum);
                        allResponseItems = response.filtered_data;
                        $('#calcButton').prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        $("#loader").removeClass('d-block').addClass('d-none');
                        $('#calcButton').prop('disabled', false);


                        console.log(xhr, status, error);
                        // Handle the error, if any
                        // $('#name-error').text(xhr.responseJSON.message);
                        // $('#saveButton').prop("disabled", false);

                    }
                });
            });

            // Save Invantory

            $('#saveInvantoryBtn').on('click', function() {
                $("#loader").addClass('d-block');
                $.ajax({
                    url: "{{ route('save_invantory') }}",
                    method: "POST",
                    data: {
                        allResponseItems,
                        typeFilter: $('#type').val(),
                        caliberFilter: $('#caliber').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $('#invantorySaveMessage').removeClass('d-none')
                        $("#loader").removeClass('d-block').addClass('d-none');
                        $("#saveInvantoryBtn").removeClass('d-none').addClass('d-block');
                    },
                    error: function(xhr, status, error) {
                        $("#loader").removeClass('d-block').addClass('d-none');
                        console.log(xhr, status, error);
                        // Handle the error, if any
                        // $('#name-error').text(xhr.responseJSON.message);
                        // $('#saveButton').prop("disabled", false);
                    }
                });
            });

            // Add event listener to the 'type' dropdown
            $('#type').on('change', function() {
                // Get the selected value
                var selectedText = $(this).find('option:selected').text();


                // Update the span with the selected value
                $('#invantoryType').text(selectedText);
            });


            // Add event listener to the 'Caliber' dropdown
            $('#caliber').on('change', function() {
                // Get the selected value
                var selectedText = $(this).find('option:selected').text();


                // Update the span with the selected value
                $('#invantoryCaliber').text(selectedText);
            });
        });
    </script>
@endsection
