@extends('layouts.app')

@section('content')
   <div class="row">
    <div class="col-sm-12 col-md-3">
        <table class="table tabel-bordered">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>الوزن</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $product->type->name }}</td>
                    <td>{{ $product->weight }}</td>
                </tr>
            </tbody>
        </table>
    </div>
   </div>

   <div class="row">
    <div class="col-sm-12 col-md-4">
        <form action="{{ route('products.refund_quantity') }}" method="POST">
            @csrf
            <div class="form-group">
                <select name="type" id="" class="form-control">
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-success">حفظ</button>
        </form>
    </div>
   </div>
@endsection