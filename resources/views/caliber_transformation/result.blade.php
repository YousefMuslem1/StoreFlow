<div class="container">
    <h4 class="mb-2">تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>العيار</th>
                <th>النوع</th>
                <th>الوزن الاصلي ب الغرام</th>
                <th>قيمة التحويل</th>
                <th> الوزن بعد التحويل الى عيار 24 ب الغرام</th>
            </tr>
        </thead>
        <tbody>
            @php
                $caliberSum = 0;
            @endphp
            @foreach ($sums as $caliberName => $caliberData)
                @php
                    $hasDisplayedRow = false;
                @endphp
                @foreach ($caliberData as $productType => $productData)
                    @if ($productData['original'] > 0)
                        @if (!$hasDisplayedRow)
                            <tr>
                                <td rowspan="{{ count($caliberData) }}">{{ $caliberName }}</td>
                                @php $hasDisplayedRow = true; @endphp
                        @endif
                        <td>{{ $productType }}</td>
                        <td>{{ $productData['original'] }}</td>
                        @foreach ($calibers as $caliber)
                            @if ($caliber->full_name == $caliberName)
                                <td>{{ $caliber->transfarmed }}</td>
                            @endif
                        @endforeach
                        <td>{{ $productData['sum'] }}</td>
                        </tr>
                        @php
                            $caliberSum += $productData['sum'];
                        @endphp
                    @endif
                @endforeach
            @endforeach

            <tr>
                <td colspan="3" style="text-align: right;"> <b>المجموع الكلي ب الكيلو غرام عيار 24:</b> </td>
                <td><b>{{ $caliberSum > 1000 ? $caliberSum / 1000 : $caliberSum }}
                        {{ $caliberSum > 1000 ? ' kg' : ' g' }} </b></td>
            </tr>
        </tbody>

    </table>


</div>
