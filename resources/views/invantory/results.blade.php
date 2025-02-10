<div>
    @if ($filtered_data)
        <div class="table-container">
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th> #</th>
                        <th> المعرف</th>
                        <th>رمز المنتج</th>
                        {{-- <th>الاسم</th> --}}
                        <th>{{ __('types.type') }}</th>
                        <th>{{ __('caliber.caliber') }}</th>
                        <th>الوزن</th>
                        {{-- <th>السعر </th> --}}
                        {{-- <th>سعر المبيع </th> --}}
                        <th>الحالة </th>
                        {{-- <th>تاريخ البيع</th> --}}
                        {{-- <th>البائع</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($filtered_data as $key => $data)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data->ident }}</td>
                            <td>{{ $data->short_ident }}</td>
                            {{-- <td>{{ $data->name ?? '-' }}</td> --}}
                            <td>{{ $data->type->name }}</td>
                            <td>{{ $data->caliber->name ?? '-' }}</td>
                            <td>{{ $data->weight ?? $data->weight_quantities_sum_weight }} g</td>
                            {{-- <td>{{ $data->calculateSellingPrice() }}</td> --}}
                            {{-- <td>{{ $data->selled_price ?? '-' }}</td> --}}
                            <td><span
                                    class="@if ($data['condition'] == 'sold') bg-orange p-1
                        @elseif ($data['condition'] == 'notavailable')
                            bg-danger p-1
                        @elseif ($data['condition'] == 'available')
                            bg-success p-1 @endif">
                                    @if ($data['condition'] == 'sold')
                                        مباع
                                    @elseif ($data['condition'] == 'notavailable')
                                        غير متوفر
                                    @elseif ($data['condition'] == 'available')
                                        متوفر
                                    @endif
                                </span></td>
                            {{-- <td>{{ $data->selled_date ?? '-' }}</td> --}}
                            {{-- <td>{{ $data->user->name ?? '-' }}</td> --}}
                            <td>
                                @if (!$data->weight_quantities_sum_weight)
                                    <a href="{{ route('products.show', $data->id) }}" target="_blank"
                                        class="btn btn-link">تفاصيل</a>
                                @endif
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        {{-- {{ $paginator->links() }} --}}
    @else
        <div class="alert alert-success mt-4"> المخزون مطابق تماماً لعملية الجرد!</div>
    @endif
</div>
