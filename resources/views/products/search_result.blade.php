<div>

    @if (!$products->isEmpty())
        @foreach ($products as $product)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product->short_ident }}</td>
                {{-- <td>{{ $product->name ?? '-' }}</td> --}}
                <td>{{ $product->type->name }}</td>
                <td>{{ $product->caliber->full_name }}</td>
                <td> {{ $product->selled_price > 0 ? $product->selled_price . ' €' : '-' }}</td>

                <td>{{ $product->weight }}</td>
                <td> {{ $product->selled_price > 0 ? number_format($product->selled_price / $product->weight, 2) : '-' }}
                </td>
                <td>{{ $product->user->name ?? '-' }}</td>
                @php
                if ($product->status == 1) {
                    $statusText = 'مباع';
                } elseif ($product->status == 3) {
                    $statusText = 'تالف';
                } elseif ($product->status == 7) {
                    $statusText = 'محجوز';
                }else {
                    $statusText = 'متوفر';
                }
            @endphp
                <td><span
                        class="@if ($product->status == 1) bg-danger p-1 @elseif ($product->status == 3) @elseif ($product->status == 7) bg-orange p-1 @else bg-success p-1 @endif">{{ $statusText }}</span>
                </td>
                <td class="d-flex justify-center">

                        <a href="{{ Auth::user()->type == 1 ? route('products.show', $product->id) : route('dataentry.show', $product->id)  }}" target="_blank"
                            class="btn btn-secondary text-white btn-sm "><i class="fas fa-eye"></i></a>
                    @if (auth()->user()->type == 1)
                        <a href="{{ route('products.edit', $product->id) }}" target="_blank"
                            class="btn btn-primary text-white btn-sm mx-1">{{ __('buttons.edit') }}</a>
                        <form action="{{ route('products.destroy', $product->id) }}" method="post" id="deleteForm"
                            onsubmit="return confirmDelete()">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm delete-button "
                                type="submit">{{ __('buttons.delete') }}</button>
                        </form>
                        @if ($product->status == 2)
                            <form action="{{ route('products.sell', $product->id) }}" method="post" id="sellForm"
                                onsubmit="return confirmSell({{ $product->id }})">
                                @csrf
                                @method('post')
                                <input type="hidden" id="new_price_{{ $product->id }}" name="new_price"
                                    value="">
                                <button class="btn btn-info btn-sm btn-info delete-button mx-1" type="submit">
                                    بيع
                                </button>
                            </form>
                        @endif
                        @if ($product->status == 1)
                            <a class="btn btn-sm btn-warning mx-1" href="{{ route('products.refund', $product->id) }}"
                                target="_blank" rel="noopener noreferrer">استرجاع</a>
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan='8' class="alert alert-warning">لايوجد بيانات لعرضها</td>
        </tr>
    @endif
</div>
