<div>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>العميل</th>
                <th>الوزن</th>
                <th>التكلفة الأولية</th>
                <th>التلكفة النهائية</th>
                <th>الحالة</th>
                <th>المسؤول</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($allMaintenances as $maintenance)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $maintenance->customer->name }}</td>
                    <td>{{ $maintenance->weight }} g</td>
                    <td>{{ $maintenance->cost }} €</td>
                    <td>{{ $maintenance->last_cost > 0 ? $maintenance->last_cost : 'غير محدّد' }} </td>
                    <td>{{ $maintenance->status_name }}</td>
                    <td>{{ $maintenance->user->name }}</td>
                    <td><a href="#">تفاصيل</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>إجمالي المبلغ: {{ $totalMaintenacesValue }} €</p>
</div>
