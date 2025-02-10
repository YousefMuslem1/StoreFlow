@extends('layouts.app')

@section('content')
    <div class="container">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>النوع</th>
                    <th>العيار</th>
                    <th>التاريخ</th>
                    <th>العمليات</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($invantories as $invantory)
                    <tr>
                        <td>{{ $invantory->id }}</td>
                        <td>{{ $invantory->type->name ?? 'الكل' }}</td>
                        <td>{{ $invantory->caliber->name ?? 'الكل' }}</td>
                        <td>{{ $invantory->created_at }}</td>
                        <td><a href="{{ route('invanotory_details', $invantory->id) }}" target="_blank"
                                class="btn  btn-primary text-white btn-sm">تفاصيل</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $invantories->links() }}
    </div>
@endsection
