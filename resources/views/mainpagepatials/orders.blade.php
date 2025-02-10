<div class="row">
    <div class="col-md-6">
        <table class="table">
            <thead>
                <th>#</th>
                <th>العميل</th>
                <th>العربون</th>
                <th>المسؤول</th>
                <th>الحالة</th>
                <th>دفعة</th>
                <th></th>
            </thead>
            <tbody>

                @foreach ($lastPayments['results'] as $result)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $result['customer_name'] }}</td>
                    <td>{{ $result['amount_paid_in_pending'] }} <b>€</b></td> <!-- New column data -->

                    <td>{{ $result['user'] }}</td>
                    <td>{{ $result['status'] }}</td>
                    <td>{{ $result['adjusted_amount'] }} <b>€</b></td>
                    <td><a href="{{ route('orders.show', $result['order_id']) }}">Details</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>