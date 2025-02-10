@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-12 col-md-4">
            <form action="{{ auth()->user()->type == 1 ? route('costs_types.store') : route('costs_types.entry.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">نوع المصروف</label>
                    <input type="text" id="name" name="type" class="form-control" autofocus req>
                </div>
                <button type="submit" class="btn btn-success mt-2">حفظ</button>
            </form>
        </div>
    </div>
@endsection
