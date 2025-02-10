@extends('layouts.app')

@section('content')
@if (Auth::user()->type != 3)
<a href="{{ auth()->user()->type == 1 ? route('costs_types.create') : route('costs_types.entry.create') }}" class="btn btn-success">جديد <i class="fas fa-plus"></i></a>
    
@endif

    <table class="table table-bordered mt-2">
        <thead>
            <tr>
                <th>#</th>
                <th>النوع</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($types as $type)
                <tr>
                    <td>{{ ($types->currentPage() - 1) * $types->perPage() + $loop->iteration }}</td>
                    <td>{{ $type->type }}</td>
                    <td>
                        <a href="#" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div id="paginate">
        {{ $types->appends(request()->query())->links() }}
    </div>
@endsection
