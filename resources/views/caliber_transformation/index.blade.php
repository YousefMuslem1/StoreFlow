@extends('layouts.app')
@section('styles')
<style type="text/css" media="print">
    body {
        visibility: hidden;
    }

    .print-section, .print-section * {
        visibility: visible;
    }

    .print-section {
        position: absolute;
        left: 70%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        direction: rtl; /* Add right-to-left direction */
        margin-top: 50px; /* Add your desired margin value */
    }

    @media print {
        .print-section {
            display: block;
        }
    }
</style>


@endsection
@section('content')
    <form>
        <div class="row d-none">
            <div class="col-md-3">
                <label for="fromDate">من:</label>
                <input type="date" class="form-control" id="fromDate" name="fromDate" value="2024-01-01">
            </div>
            <div class="col-md-3">
                <label for="toDate">الى:</label>
                <input type="date" class="form-control" id="toDate" name="toDate" value="2024-12-31">
            </div>
            <div class="col-md-3">
                <label for="filterType">تصفية بالتاريخ؟</label>
                <select class="form-control" id="filterType" name="filterType">
                    <option value="allProducts">كل المنتجات</option>
                    <option value="dateRange">نعم</option>
                </select>
            </div>

        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="type">النوع</label>
                    <select name="type" id="type" class="form-control">
                        <option value=""> الكل</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="caliber">العيار</label>
                    <select name="caliber" id="caliber" class="form-control">
                        <option value=""> الكل</option>
                        @foreach ($calibers as $caliber)
                            <option value="{{ $caliber->id }}">{{ $caliber->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 mt-4">
                <button type="button" id="resultButton" class="btn btn-success">تنفيذ</button>
            </div>
        </div>


        <h3 class="mt-5">النتائج</h3>
        <hr>
        <button onclick="printPage()" id="printButton" class="btn btn-primary d-none">طباعة</button>
        <hr>
        <div class="print-section" id="result">

        </div>

    </form>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#resultButton').on('click', function() {
                // Get the selected value from the dropdown
                var caliber = $('#caliber').val();
                var type = $('#type').val();
                var filterType = $('#filterType').val();
                // Your Ajax request
                $.ajax({
                    url: '{{ route('caliber_trans_result') }}', // Replace with your actual API endpoint
                    method: 'GET', // Use 'POST' if needed
                    data: {
                        caliber,
                        type,
                        filterType,
                    },
                    success: function(response) {
                        // Handle the success response
                        console.log('Ajax request successful:', response);
                        var ResponseContiner = document.getElementById('result');
                        ResponseContiner.innerHTML = response;
                        $('#printButton').removeClass('d-none').addClass('btn btn-primary')
                        // Add your logic to update the DOM with the retrieved data
                    },
                    error: function(xhr, status, error) {
                        // Handle the error
                        console.error('Ajax request error:', error);
                    }
                });
            });
        });
    </script>
    <script>
        function printPage() {
            window.print();
        }
    </script>
@endsection
