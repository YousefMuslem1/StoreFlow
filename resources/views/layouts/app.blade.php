<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Gallwery Store</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@385&display=swap" rel="stylesheet">
    <!-- Theme style -->
    {{-- <link rel="stylesheet" href="{{ asset('build/assets/app-381c5ba3.css') }}">
    <script src="{{ asset('build/assets/app-eda4c780.js') }}"></script> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@241&display=swap" rel="stylesheet">
    <!-- Bootstrap 4 RTL -->
    @if (app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.rtlcss.com/bootstrap/v4.2.1/css/bootstrap.min.css">
        <!-- Custom style for RTL -->
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @endif
    @yield('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        @include('partials.nav')
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('partials.aside')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <h2 class="d-inline ">{{ $header ?? '' }}</h2>
                    <div class="row">
                        <div class="col-sm-4 col-md-12">
                            {{-- @if (Auth::user()->type != 4)
                                @isset($totalSumAfterTransforming)
                                    <span class="float-right "> الوزن الكلّي عيار 24 : <b
                                            style="font-size: 20px">{{ $totalSumAfterTransforming ?? '' }} </b></span>
                                @endisset
                            @endif

                            @isset($selledSumTodayWeight)
                                <span class="float-right ml-3"> الوزن المباع اليوم : <b
                                        style="font-size: 20px">{{ $selledSumTodayWeight ?? '' }} g</b></span>
                            @endisset
                            @isset($selledTodayCount)
                                <span class="float-right ml-3"> القطع المباعة اليوم: <b
                                        style="font-size: 20px">{{ $selledTodayCount ?? '' }} </b></span>
                            @endisset
                            @isset($newProductWeight)
                                <span class="float-right ml-3"> الوزن المدخل اليوم : <b
                                        style="font-size: 20px">{{ $newProductWeight + $newQuantityWeight ?? '' }} g
                                    </b></span>
                            @endisset
                            @isset($totalFineEnteredTodayWeigt)
                                <span class="float-right ml-3"> الوزن المدخل مقابل 24 : <b
                                        style="font-size: 20px">{{ $totalFineEnteredTodayWeigt }} g </b></span>
                            @endisset
                            @isset($selledTodayPriceTotal)
                                <span class="float-right ml-3"> الكاسة : <b
                                        style="font-size: 20px">{{ $selledTodayPriceTotal ?? '' }} € </b></span>
                            @endisset --}}

                        </div>
                    </div>
                    @yield('header_infos')
                    <hr>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
        <!-- /.content-wrapper -->
        @include('partials.footer')

    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

    <!-- Bootstrap 4 rtl -->
    <script src="https://cdn.rtlcss.com/bootstrap/v4.2.1/js/bootstrap.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>

</html>
