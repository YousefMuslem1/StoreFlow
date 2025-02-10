<!-- resources/views/sales-chart.blade.php -->

@extends('layouts.app')
<style>
    /* CSS to change cursor to pointer on hover */
    #hideShowStatisticsDetails:hover,
    #hideShowStatisticsCaliber:hover,
    #hideShowStatisticsPublic:hover {
        cursor: pointer;
    }

    #statisticsDetails,
    #statisticsCaliber,
    #statisticsPublic {

        transition: all 0.5s ease;
        /* Adjust the duration and timing function as needed */
    }

    .table-container {
        overflow-x: auto;
    }

    .highlight {
        background-color: yellow;
        /* You can change this to any color you want */
    }
</style>
@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-5">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>القطع المباعة اليوم</th>
                        <td>{{ $selledTodayCount ?? '' }} </td>
                    </tr>
                    <tr>
                        <th>الوزن المباع اليوم </th>
                        <td>{{ $selledTodayCount ?? '' }}  g</td>
                    </tr>
                    <tr>
                        <th>الوزن المدخل اليوم </th>
                        <td>{{ $newProductWeight + $newQuantityWeight ?? '' }} g</td>
                    </tr>
                    <tr>
                        <th>الوزن المدخل مقابل 24 </th>
                        <td>{{ $totalFineEnteredTodayWeigt }} g</td>
                    </tr>
                    <tr>
                        <th>الكاسة </th>
                        <td>{{ $selledTodayPriceTotal - $newBoughtPrice ?? '' }} €</td>
                    </tr> 
                </tbody>
            </table>
        </div>
    </div>
    <hr>
    <h4 class="d-inline my-3 "> تسعيرة العيار</h4> <span id="hideShowStatisticsCaliber" class="cursor-pointer">عرض</span>

    <div class="row my-2" id="statisticsCaliber" style="display: none;">
        @foreach ($calibers as $caliber)
            <div class="col-sm-6">
                <span class="font-weight-bold">العيار: <span class="badge badge-danger">{{ $caliber->full_name }}</span> -
                </span>
                <span> السعر <span class="badge badge-success"> <b>€</b>{{ $caliber->caliber_price }}</span></span>
            </div>
        @endforeach
    </div>
    <hr>
    <h3 class="d-inline my-3 ">احصائيات عامة</h3> <span id="hideShowStatisticsPublic" class="cursor-pointer">عرض</span>
    <div class="row" id="statisticsPublic" style="display: none;">
        @php
            $totalWeight = [];
            $totalProducts = [];
            $transormation = [];
            $quantities = [];
        @endphp

        <!-- product and quantity has same caliber name -->
        @if ($sumWeights)
            @foreach ($sumWeights as $caliber => $data)
                @php
                    $totalWeight[$caliber] = $data['total_weight']; // Initialize with sumWeights total_weight
                    $totalProducts[$caliber] = $data['total_products']; // Initialize with sumWeights total_products
                    $transormation[$caliber] = $data['multiplied_value']; // Initialize with sumWeights total_products
                @endphp
                <!-- product and quantity has same caliber name -->
                @foreach ($quantitestotalWeights as $key => $quantityWeight)
                    @if ($caliber == $quantityWeight['caliber'])
                        @php
                            $totalProducts[$caliber] += 1;
                            $totalWeight[$caliber] += $quantityWeight['total_weight']; // Add quantityWeight total_weight
                            $transormation[$caliber] += $quantityWeight['multiplied_value']; // Add quantityWeight total_weight
                            // Increment totalProducts only if a new product is encountered
                            unset($quantitestotalWeights[$key]);
                        @endphp
                    @endif
                @endforeach
            @endforeach
            @foreach ($totalWeight as $caliber => $weight)
                <div class="col-md-3">
                    <div class="small-box bg-{{ $bgColors[$loop->index] }}">
                        <div class="inner">
                            <h4 class="text-white font-weight-bold"> {{ $caliber }}</h4>
                            @php
                                $formattedWeight =
                                    $weight > 1000 ? number_format($weight / 1000, 2) : number_format($weight, 2);
                            @endphp

                            <h6 class="text-white"> الوزن الكلّي : {{ $formattedWeight }} {{ $weight > 1000 ? 'kg' : 'g' }}
                            </h6>

                            <h6 class="text-white"> عدد المنتجات: {{ $totalProducts[$caliber] }}</h6>
                            <h6 class="text-white"> مقابل عيار 24 =
                                {{ $transormation[$caliber] > 1000 ? number_format($transormation[$caliber] / 1000, 3) : number_format($transormation[$caliber], 3) }}
                                {{ $transormation[$caliber] > 1000 ? 'kg' : 'gr' }} </h6>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        @if ($quantitestotalWeights)
            @foreach ($quantitestotalWeights as $quantityWeight)
                <div class="col-md-3">
                    <div class="small-box bg-{{ $bgColors[$loop->index % count($bgColors)] }}">
                        <div class="inner">
                            <h4 class="text-white font-weight-bold"> {{ $quantityWeight['caliber'] }}</h4>
                            <h6 class="text-white"> الوزن الكلّي
                                :{{ $quantityWeight['total_weight'] > 1000 ? $quantityWeight['total_weight'] / 1000 : $quantityWeight['total_weight'] }}
                                {{ $quantityWeight['total_weight'] > 1000 ? 'kg' : 'gr' }}</h6>
                            {{-- <h6 class="text-white"> عدد المنتجات: {{ $quantityWeight['total_products'] }}</h6> --}}
                            @php
                                $totalWeight = $quantityWeight['multiplied_value'];
                            @endphp
                            <h6 class="text-white"> مقابل عيار 24 =
                                {{ $totalWeight > 1000 ? $totalWeight / 1000 : $totalWeight }}
                                {{ $totalWeight > 1000 ? 'kg' : 'gr' }} </h6>
                            <h6 class="text-white">القيمة التحويلية: {{ $quantityWeight['transfarmed_value'] }}</h6>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif


    </div>
    <hr>
    <h4 class="d-inline my-3 ">احصائيات مفصّلة</h4> <span id="hideShowStatisticsDetails" class="cursor-pointer">عرض</span>
    <div class="my-3">
        <div class="row" id="statisticsDetails" style="display: none;">
            @foreach ($weights as $typeName => $calibers)
                <div class="col-md-3">
                    <div class="small-box bg-{{ $bgColors[$loop->index % count($bgColors)] }}">
                        <div class="inner">
                            <h4 class="text-white font-weight-bold">{{ $typeName }}</h4>
                            @foreach ($calibers as $caliberName => $weight)
                                @if ($caliberName !== 'total')
                                    <!-- Exclude 'Total' key -->
                                    <h6 class="text-white"> عيار {{ $caliberName }}: الوزن الكلّي {{ $weight }}
                                        g
                                    </h6>
                                @endif
                            @endforeach
                            <h6 class="text-white">عدد المنتجات: {{ $calibers['total'] }}</h6>
                            <!-- Display total count -->

                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                    </div>
                </div>
            @endforeach
            {{-- {{ var_dump($quantitestotalWeights) }} --}}
            @foreach ($detialedQuantites as $caliber => $data)
                <div class="col-md-3">
                    <div class="small-box bg-{{ $bgColors[$loop->index % count($bgColors)] }}">
                        <div class="inner">
                            <h4 class="text-white font-weight-bold">{{ $data['type'] }}</h4>
                            <h6>الوزن الكلي: {{ $data['total_weight'] }} g</h6>
                            <h5>العيار: {{ $data['caliber'] }}</h5>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <canvas id="weightChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Today sales -->
    <h3>أوزان مباعة</h3>
    <input type="text" id="filterInput" placeholder="بحث">
    <div class="row">
        <div class="col-md-8">
            <div class="table-container">
                <table class="table" id="selledTable">
                    <thead>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>العيار</th>
                        <th>الصنف</th>
                        <th>سعر المبيع</th>
                        <th>الوزن</th>
                        <th>سعر الغرام</th>
                        <th>البائع</th>
                        <th></th>
                    </thead>
                    <tbody>
                        @foreach ($selled_products as $selled_product)
                            <tr class="text-{{ !empty($selled_product->description) ? 'success' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $selled_product->short_ident }}</td>
                                <td>{{ $selled_product->caliber->full_name }}</td>
                                <td>{{ $selled_product->type->name }}</td>
                                <td>{{ $selled_product->selled_price }} <b>€</b></td>
                                <td>{{ $selled_product->weight }}</td>
                                <td>{{ number_format($selled_product->selled_price / $selled_product->weight, 2) }}</td>
                                <td>{{ $selled_product->user->name }}</td>
                                <td><a target="_blank" href="{{ route('dataentry.show', $selled_product->id) }}">تفاصيل</a>
                                    {{-- <td>
                                    @if ($selled_product->short_ident)
                                        <form class="m-0" action="{{ route('product_reset') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="product" value="{{ $selled_product->id }}">
                                            <button class="btn btn-link text-danger p-0 m-0">الغاء</button>
                                        </form>
                                    @endif
                                </td> --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <hr>
    <h3> مشتراه</h3>
    @include('mainpagepatials.bought_quantity')
    <hr>

    <h3> أوزان جديدة</h3>
    @include('mainpagepatials.new_products')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#filterInput').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();

                $('#selledTable tbody tr').each(function() {
                    var rowNumber = $(this).find('td:nth-child(2)')
                        .text(); // Select the second column

                    // Check if the row number contains the search text
                    if (rowNumber.includes(searchText) && searchText !== '') {
                        $(this).addClass('highlight');
                    } else {
                        $(this).removeClass('highlight');
                    }
                });
            });
        });

        var ctx = document.getElementById('salesChart').getContext('2d');
        var weights = document.getElementById('weightChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($dates),
                datasets: [{
                    label: 'اجمالي المبيعات اليومية',
                    data: @json($totalSales),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)'
                    ],
                    hoverBorderColor: 'red',
                    color: '#9966FF',
                    borderWidth: 1
                }]
            }
        });

        var myChart = new Chart(weights, {
            type: 'line',
            data: {
                labels: @json($dates),
                datasets: [{
                    label: 'اجمالي الأوزان المباعة اليومية ',
                    data: @json($totalSelledWeight),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)'
                    ],
                    hoverBorderColor: 'red',
                    color: '#9966FF',
                    borderWidth: 1
                }]
            }
        });

        // hide show statistics details
        $("#hideShowStatisticsDetails").click(function() {
            $("#statisticsDetails").toggle();
            // Toggle text between "إخفاء" and "عرض"
            $(this).text(function(i, text) {
                return text === "عرض" ? "إخفاء" : "عرض";
            });
        });

        // hide show statistics caliber
        $("#hideShowStatisticsCaliber").click(function() {
            $("#statisticsCaliber").toggle();
            // Toggle text between "إخفاء" and "عرض"
            $(this).text(function(i, text) {
                return text === "عرض" ? "إخفاء" : "عرض";
            });
        });
        // hide show statistics caliber
        $("#hideShowStatisticsPublic").click(function() {
            $("#statisticsPublic").toggle();
            // Toggle text between "إخفاء" and "عرض"
            $(this).text(function(i, text) {
                return text === "عرض" ? "إخفاء" : "عرض";
            });
        });
    </script>
@endsection
