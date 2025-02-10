@extends('employer.layouts.app')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"> {{ $customer->name }}</h1>
    </div>
    <hr>

    <table class="table table-bordered">
        <thead>
            <th>#</th>
            <th>رقم المنتج</th>
            <th>سعر المبيع</th>
            <th>المدفوع</th>
            
            <th></th>
        </thead>
    </table>
@endsection
