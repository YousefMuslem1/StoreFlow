@extends('layouts.app')

@section('content')
    <form action="{{ route('quantities.quantity_details', $quantity) }}" method="GET">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="from">من</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ $from }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="to">إلى</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ $to }}">
                </div>

            </div>
            <div class="col-md-2 mt-md-4">
                <button type="submit" class="btn btn-primary">تنفيذ</button>

            </div>
        </div>

    </form>
    <div class="row">

        <div class="col-sm-12 col-md-3">
            <table class="table table-bordered">
                <tr>
                    <th>الوزن المتوفر</th>
                    <th class="text text-success">g{{ $total_weight }}</th>
                </tr>
            </table>
        </div>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>الوزن</th>
                <th>الحالة</th>
                <th>المنتج المرتبط</th>
                <th>الموظف</th>
                <th>تاريخ الانشاء</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach ($quantities as $quantity)
                <tr>
                    <td>{{ ($quantities->currentPage() - 1) * $quantities->perPage() + $loop->iteration }}</td>
                    <td class="text text-{{ $quantity->weight > 0 ? 'success' : 'danger' }}">{{ $quantity->weight }} g</td>
                    <td> {{ getStatusName($quantity->status) }}</td>
                    <td>
                        @if ($quantity->product_id)
                            <a href="{{ route('products.show', $quantity->product_id) }}" target="_blank">المنتج({{ $quantity->product->short_ident }})</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $quantity->user->name }}</td>
                    <td>{{ $quantity->created_at }}</td>
                    <td class="d-flex">
                        <a href="{{ route('quantities.edit', $quantity->id) }}" class="btn btn-primary btn-sm"><i
                                class="fas fa-edit"></i></a>
                        <a href="{{ route('quantities.detail', $quantity->id) }}" class="btn btn-secondary btn-sm mx-1"><i
                                class="fas fa-eye"></i></a>
                        @if ($quantity->status == 9 || $quantity->status == 8)
                            <form action="{{ route('quantities.destroy') }}" method="POST"
                                onsubmit="return confirmDelete()">
                                @csrf
                                @method('delete')
                                <input type="hidden" name="quantity" value="{{ $quantity->id }}">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
    <div id="paginate">
        {{ $quantities->appends(request()->query())->links() }}
    </div>
@endsection

@section('scripts')
    <script>
        function confirmDelete() {
            return confirm('هل انت متأكد من حذف هذا العنصر?');
        }
    </script>
@endsection
