@extends('layouts.app')

@section('content')
    <form action="{{ route('quantities.edit.store', $quantity->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <div class="form-group">
                    <label for="data">تاريخ الادخال</label>
                    <input type="date" name="created_at" class="form-control"
                        value="{{ \Carbon\Carbon::parse($quantity->created_at)->format('Y-m-d') }}">
                </div>
            </div>
        </div>
        @if ($quantity->status != 2 || $quantity->status != 5)
            <div class="row">
                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label for="weight">الوزن</label>
                        <input type="number" step="0.01" name="weight" class="form-control" value="{{ $quantity->weight }}">
                    </div>
                </div>
            </div>
        @endif

        <button type="submit" class="btn btn-success"> حفظ</button>
    </form>
@endsection
